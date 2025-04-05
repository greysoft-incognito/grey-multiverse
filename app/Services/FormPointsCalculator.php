<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormData;
use Illuminate\Support\Collection;

class FormPointsCalculator
{
    /**
     * Calculates the total points earned by a user based on their submitted FormData.
     *
     * This method iterates over all FormField records, matches them with the user's answers
     * in FormData->data, and computes points according to the following rules:
     * - Skipped questions (missing, null, or empty answers) award 0 points.
     * - For fields with options (e.g., select, checkboxgroup), awards base field points plus
     *   the sum of points from matched options.
     * - For fields without options (e.g., input), awards base field points if the answer is
     *   non-empty and matches the expected value type.
     *
     * @param FormData $formData The FormData instance containing the user's submitted answers.
     *
     * @return int The total points earned by the user.
     */
    public function calculatePoints(FormData $formData): int
    {
        $formFields = $formData->form->fields->keyBy('name'); // Key by name for lookup
        $userAnswers = $formData->data; // User's submitted answers
        $totalPoints = 0;

        foreach ($formFields as $fieldName => $formField) {
            if (
                !$userAnswers->has($fieldName) ||
                $userAnswers[$fieldName] === null ||
                $userAnswers[$fieldName] === ''
            ) {
                continue;
            }

            $userAnswer = $userAnswers[$fieldName];
            $fieldPoints = (int) $formField->points;
            $expectedType = $formField->expected_value_type;

            if (!empty($formField->options) && is_array($formField->options)) {
                $optionsPoints = $this->calculatePointsFromOptions($formField, $userAnswer, $expectedType);
                $totalPoints += $fieldPoints + $optionsPoints; // Add both
            } else {
                if ($this->isValidAnswer($userAnswer, $expectedType)) {
                    $totalPoints += $fieldPoints; // Only field points
                }
            }
        }

        return $totalPoints;
    }

    /**
     * Calculates points from a user's answer for a FormField with options.
     *
     * This method evaluates the user's answer against the field's options array:
     * - For fields expecting an array (e.g., checkboxgroup), sums points for all matching option values.
     * - For fields expecting a single value (e.g., select, radiogroup), awards points for the first matching option.
     * - Invalid or unmatched answers contribute 0 points.
     * - Option values and user answers are cast to strings for comparison.
     *
     * @param FormField $formField The FormField instance containing options and expected type.
     * @param mixed $userAnswer The user's answer (string, int, or array) from FormData->data.
     * @param string $expectedType The expected PHP type of the answer (e.g., 'string', 'array').
     *
     * @return int The total points from matched options.
     */
    private function calculatePointsFromOptions(FormField $formField, $userAnswer, string $expectedType): int
    {
        $options = $formField->options ?? [];
        $points = 0;

        // Handle multiple answers (checkboxgroup) or single answer (select, radiogroup)
        $answers = ($expectedType === 'array' && is_array($userAnswer))
            ? $userAnswer
            : [$userAnswer];

        foreach ($answers as $answer) {
            // Match answer to an option (assume option values are strings)
            foreach ($options as $option) {
                if (isset($option['value']) && (string) $option['value'] === (string) $answer) {
                    $points += $option['points'] ?? 0;
                    break; // Move to next answer once matched
                }
            }
        }

        return $points;
    }

    /**
     * Validates whether a user's answer is acceptable for a FormField without options.
     *
     * This method checks if the answer is non-empty and reasonably matches the expected type:
     * - 'string': Accepts strings or scalars (e.g., numbers that can be cast).
     * - 'integer': Accepts integers or numeric strings that convert cleanly to integers.
     * - 'boolean': Accepts booleans or values like 0, 1, '0', '1'.
     * - 'array': Accepts arrays.
     * - 'mixed' or unknown: Accepts any non-empty value.
     * - Null or empty string answers are invalid.
     *
     * @param mixed $answer The user's answer from FormData->data.
     * @param string $expectedType The expected PHP type of the answer (e.g., 'string', 'integer').
     *
     * @return bool True if the answer is valid, false otherwise.
     */
    private function isValidAnswer($answer, string $expectedType): bool
    {
        // Skip empty answers
        if (is_null($answer) || $answer === '') {
            return false;
        }

        // Basic type checking (not strict, just reasonable compatibility)
        return match ($expectedType) {
            'string' => is_string($answer) || is_scalar($answer), // Allow numbers to be cast
            'integer' => is_int($answer) || (is_numeric($answer) && (int) $answer == $answer),
            'boolean' => is_bool($answer) || in_array($answer, [0, 1, '0', '1', true, false], true),
            'array' => is_array($answer),
            'mixed' => true, // Fallback for undefined types
            default => is_string($answer), // Default to string as per your attribute
        };
    }

    /**
     *
     * @param Form $form
     * @param integer $take
     *
     * @return array{labels: string[], datasets: array<string|int, array{label: mixed, data: (int|float|mixed)[], backgroundColor: string, borderColor: string, borderWidth: int}>}
     */
    public function questionsChartData(Form $form, int $take = 0)
    {
        $questions = $this->questionStats($form, $take);

        $labels = $questions->pluck('question')->all();
        $answeredData = $questions->map(fn($item) => $item['submissions_count'] - $item['unanswered_count'])->all();
        $unansweredData = $questions->pluck('unanswered_count')->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Answered',
                    'data' => $answeredData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)', // Blue-ish
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Unanswered',
                    'data' => $unansweredData,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)', // Red-ish
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    /**
     * Retrieves the 5 most frequently unanswered FormFields across all FormData submissions.
     *
     * This method uses a database query to analyze the JSON keys in FormData->data,
     * counting how often each FormField is missing (i.e., not present as a key).
     * It returns the top 5 FormFields with the highest unanswered counts, sorted in descending order.
     *
     * @param Form $form
     * @param integer $take
     *
     * @return Collection A collection of the 5 most unanswered FormFields, each with 'field' (FormField) and 'unanswered_count'.
     */
    public function questionStats(Form $form, int $take = 0): Collection
    {
        $formFields = $form->fields->keyBy('name');
        $totalSubmissions = $form->data()->count();

        // Initialize counts for all fields
        $unansweredCounts = $formFields->mapWithKeys(function (FormField $field) {
            return [$field->name => 0];
        })->all();

        // Get all answered keys from FormData records
        $formDataRecords = $form->data()
            ->selectRaw("JSON_KEYS(data) as answered_keys")
            ->whereNot('data', '[]')
            ->whereNot('data', '{}')
            ->get();

        // Count unanswered instances
        foreach ($formDataRecords as $formData) {
            $answeredKeys = json_decode($formData->answered_keys, true) ?? [];

            foreach ($formFields as $fieldName => $field) {
                if (!in_array($fieldName, $answeredKeys)) {
                    $unansweredCounts[$fieldName]++;
                }
            }
        }

        // Sort and return top 5
        return collect($unansweredCounts)
            ->map(function ($count, $fieldName) use ($formFields, $totalSubmissions) {
                $unansweredPercentage = $totalSubmissions > 0
                    ? round(($count / $totalSubmissions) * 100, 2)
                    : 0;
                $answeredPercentage = $totalSubmissions > 0
                    ? round((($totalSubmissions - $count) / $totalSubmissions) * 100, 2)
                    : 0;

                return [
                    'field' => $formFields[$fieldName]->name,
                    'question' => str($formFields[$fieldName]->label)->words(5, '[...]')->toString(),
                    'unanswered_count' => $count,
                    'submissions_count' => $totalSubmissions,
                    'answered_percentage' => $answeredPercentage,
                    'unanswered_percentage' => $unansweredPercentage,
                ];
            })
            ->sortByDesc('unanswered_count')
            ->when($take, fn($e) => $e->take($take))
            ->values();
    }

    /**
     * Get the total maximum achievable points for the form.
     *
     * This attribute calculates the maximum possible points by summing each field's base points
     * and the maximum or sum of its option points, depending on the field's expected value type:
     * - For fields with 'array' expectedValueType, sums all positive option points (multi-select).
     * - For other fields (e.g., 'string', 'integer'), takes the highest option points (single-select).
     * - Fields without options contribute only their base points.
     *
     * @param Form $form
     * @return integer
     */
    public function calculateFormTotalPoints(Form $form): int
    {
        return  $form->fields->sum(function ($field): int {
            $fieldPoints = (int) $field->points;
            $parser = new \App\Services\PointsScript\PointsScriptParser();

            if ($field->points_script) {
                // Simulate max answer to get highest possible points
                $maxAnswer = $parser->getMaxAnswer($field);
                return $parser->evaluate($field->points_script, $maxAnswer);
            }

            $optionsPoints = 0;

            if (!empty($field->options) && is_array($field->options)) {
                $positiveOptions = collect($field->options)
                    ->filter(fn($opt) => isset($opt['points']) && (int) $opt['points'] > 0);

                $optionsPoints = $field->expected_value_type === 'array'
                    ? $positiveOptions->sum(fn($opt) => (int) $opt['points']) // Sum for multi-select
                    : $positiveOptions->max(fn($opt) => (int) $opt['points']) ?? 0; // Max for single-select
            }

            return $fieldPoints + $optionsPoints;
        });
    }
}

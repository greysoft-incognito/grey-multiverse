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
     * Generates a color for Chart.js datasets based on index.
     *
     * @param int $index The index of the dataset (0-4 for top 5).
     * @param float $alpha The alpha transparency (default 0.5 for fill, 1 for border).
     * @return string RGBA color string.
     */
    private function getColor(int $index, float $alpha = 0.5): string
    {
        $colors = [
            'rgba(255, 99, 132, ALPHA)',  // Red
            'rgba(54, 162, 235, ALPHA)',  // Blue
            'rgba(255, 206, 86, ALPHA)',  // Yellow
            'rgba(75, 192, 192, ALPHA)',  // Teal
            'rgba(153, 102, 255, ALPHA)', // Purple
        ];
        return str_replace('ALPHA', $alpha, $colors[$index % 5]);
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

        return [
            'labels' => ['Answered', 'Unanswered'], // Fixed labels for each bar
            'datasets' => $questions->map(function ($item, $index) {
                $answeredCount = $item['submissions_count'] - $item['unanswered_count'];
                return [
                    'label' => $item['question'],
                    'data' => [$answeredCount, $item['unanswered_count']],
                    'backgroundColor' => $this->getColor($index), // Dynamic color per question
                    'borderColor' => $this->getColor($index, 1), // Solid border
                    'borderWidth' => 1,
                ];
            })->all(),
        ];
    }

    /**
     * Retrieves the 5 most frequently unanswered FormFields across all FormData submissions.
     *
     * This method uses a database query to analyze the JSON keys in FormData->data,
     * counting how often each FormField is missing (i.e., not present as a key).
     * It returns the top 5 FormFields with the highest unanswered counts, sorted in descending order.
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
}

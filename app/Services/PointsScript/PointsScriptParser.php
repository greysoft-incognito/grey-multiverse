<?php

namespace App\Services\PointsScript;

use App\Models\FormField;

class PointsScriptParser
{
    /**
     * Parse and evaluate a PointsScript string for a given FormField and user answer.
     *
     * @param FormField $field The FormField with points_script
     * @param mixed $userAnswer The user's answer from FormData->data
     * @return int The calculated points
     */
    public function evaluate(FormField $field, $userAnswer): int
    {
        if (empty($field->points_script)) {
            return 0; // No script, no points
        }

        $lines = explode("\n", trim($field->points_script));
        $defaultPoints = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue; // Skip empty lines and comments
            }

            // Match if statements
            if (preg_match('/if\s*\((.*?)\)\s*return\s*(\d+)/i', $line, $matches)) {
                $condition = trim($matches[1]);
                $points = (int) $matches[2];

                if ($this->evaluateCondition($condition, $field, $userAnswer)) {
                    return $points;
                }
            }
            // Match default return
            elseif (preg_match('/return\s*(\d+)/i', $line, $matches)) {
                $defaultPoints = (int) $matches[1];
            }
        }

        return $defaultPoints;
    }

    /**
     * Evaluate a condition from the PointsScript.
     *
     * @param string $condition The condition string (e.g., "count(options >= 5)" or "contains(\"good\")")
     * @param FormField $field The FormField
     * @param mixed $userAnswer The user's answer
     * @return bool Whether the condition is true
     */
    private function evaluateCondition(string $condition, FormField $field, $userAnswer): bool
    {
        // Handle count() conditions
        if (preg_match('/count\(options\s*([=<>!]+)\s*(\d+)\)/i', $condition, $matches)) {
            $operator = $matches[1];
            $value = (int) $matches[2];
            $count = is_array($userAnswer) ? count($userAnswer) : 0;

            return match ($operator) {
                '==' => $count == $value,
                '>=' => $count >= $value,
                '<=' => $count <= $value,
                '>' => $count > $value,
                '<' => $count < $value,
                '!=' => $count != $value,
                default => false,
            };
        }

        // Handle contains() conditions
        if (preg_match('/contains\("([^"]*)"\)/i', $condition, $matches)) {
            $substring = $matches[1];

            // Evaluate only if answer is a string
            if (is_string($userAnswer)) {
                return stripos($userAnswer, $substring) !== false;
            }
        }

        return false; // Unknown condition
    }

    public function getMaxAnswer(FormField $field)
    {
        if ($field->expected_value_type === 'array') {
            // For arrays, assume all options are selected
            return collect($field->options)->pluck('value')->all();
        }

        if ($field->expected_value_type === 'string') {
            // For strings, parse points_script to find all substrings and combine them
            $substrings = [];
            if ($field->points_script) {
                foreach (explode("\n", $field->points_script) as $line) {
                    if (preg_match('/contains\("([^"]*)"\)/i', $line, $matches)) {
                        $substrings[] = $matches[1];
                    }
                }
            }

            return implode(' ', $substrings) ?: 'default'; // Combine substrings or fallback
        }

        return null;
    }
}

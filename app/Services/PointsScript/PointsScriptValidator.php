<?php

namespace App\Services\PointsScript;

use InvalidArgumentException;

class PointsScriptValidator
{
    /**
     * Validate a PointsScript string.
     *
     * @param string $script The PointsScript to validate
     * @throws InvalidArgumentException If the script is invalid
     * @return bool True if valid
     */
    public function validate(string $script): bool
    {
        if (empty(trim($script))) {
            throw new InvalidArgumentException("PointsScript cannot be empty.");
        }

        $lines = explode("\n", trim($script));
        $hasReturn = false;
        $countConditions = []; // Track count() conditions to detect overlaps

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue; // Skip empty lines and comments
            }

            // Validate if statement
            if (preg_match('/if\s*\((.*?)\)\s*return\s*(\d+)/i', $line, $matches)) {
                $condition = trim($matches[1]);
                $points = (int) $matches[2];

                if (!$this->validateCondition($condition, $countConditions)) {
                    throw new InvalidArgumentException("Invalid condition in line " . ($lineNumber + 1) . ": '$condition'");
                }

                if ($points < 0) {
                    throw new InvalidArgumentException("Points must be non-negative in line " . ($lineNumber + 1) . ": '$points'");
                }
            }
            // Validate default return
            elseif (preg_match('/return\s*(\d+)/i', $line, $matches)) {
                $points = (int) $matches[1];
                if ($points < 0) {
                    throw new InvalidArgumentException("Points must be non-negative in line " . ($lineNumber + 1) . ": '$points'");
                }
                if ($hasReturn) {
                    throw new InvalidArgumentException("Multiple default returns detected at line " . ($lineNumber + 1) . ". Only one is allowed.");
                }
                $hasReturn = true;
            }
            else {
                throw new InvalidArgumentException("Invalid syntax in line " . ($lineNumber + 1) . ": '$line'");
            }
        }

        if (!$hasReturn && empty($countConditions)) {
            throw new InvalidArgumentException("No default return statement found, and no conditions cover all cases.");
        }

        return true;
    }

    /**
     * Validate a condition and check for overlaps.
     *
     * @param string $condition The condition to validate
     * @param array &$countConditions Reference to track count conditions
     * @return bool True if valid
     */
    private function validateCondition(string $condition, array &$countConditions): bool
    {
        // Validate count() condition
        if (preg_match('/count\(options\s*([=<>!]+)\s*(\d+)\)/i', $condition, $matches)) {
            $operator = $matches[1];
            $value = (int) $matches[2];

            if (!in_array($operator, ['==', '>=', '<=', '>', '<', '!='])) {
                return false;
            }

            if ($value < 0) {
                return false; // Negative counts don’t make sense
            }

            // Check for overlapping conditions
            foreach ($countConditions as $prev) {
                if ($this->isCountOverlap($prev['operator'], $prev['value'], $operator, $value)) {
                    throw new InvalidArgumentException("Condition '$condition' overlaps with previous condition 'count(options {$prev['operator']} {$prev['value']})', making later rules unreachable.");
                }
            }
            $countConditions[] = ['operator' => $operator, 'value' => $value];
            return true;
        }

        // Validate contains() condition
        if (preg_match('/contains\("([^"]*)"\)/i', $condition, $matches)) {
            $substring = $matches[1];
            if (empty($substring)) {
                return false; // Empty substring is invalid
            }
            return true;
        }

        return false; // Unknown condition
    }

    /**
     * Check if two count conditions overlap in a way that makes later rules unreachable.
     *
     * @param string $prevOperator Previous operator
     * @param int $prevValue Previous value
     * @param string $operator Current operator
     * @param int $value Current value
     * @return bool True if overlapping
     */
    private function isCountOverlap(string $prevOperator, int $prevValue, string $operator, int $value): bool
    {
        // Simplified overlap check (assumes conditions are evaluated top-down)
        if ($prevOperator === '>=' && $operator === '>=' && $value >= $prevValue) {
            return true; // e.g., >=5 covers >=4
        }
        if ($prevOperator === '==' && $operator === '==' && $value === $prevValue) {
            return true; // e.g., ==1 covers ==1
        }
        // Add more overlap logic as needed (e.g., > vs >=, <= vs <)
        return false;
    }
}

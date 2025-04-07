<?php

namespace App\Services\PointsScript;

use InvalidArgumentException;

class PointsScriptValidator
{
    /**
     * Validate a PointsScript string.
     *
     * @param string $pointsScript The PointsScript to validate
     * @throws InvalidArgumentException If the script is invalid
     * @return bool True if valid
     */
    public function validate(string $pointsScript): bool
    {
        if (empty(trim($pointsScript))) {
            throw new InvalidArgumentException("PointsScript cannot be empty.");
        }

        $lines = explode("\n", trim($pointsScript));
        $hasGive = false;
        $countConditions = [];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/if\s*\((.*?)\)\s*give\s*(\d+)/i', $line, $matches)) {
                $condition = trim($matches[1]);
                $points = (int) $matches[2];

                if (!$this->validateCondition($condition, $countConditions)) {
                    throw new InvalidArgumentException("Invalid condition in line " . ($lineNumber + 1) . ": '$condition'");
                }

                if ($points < 0) {
                    throw new InvalidArgumentException("Points must be non-negative in line " . ($lineNumber + 1) . ": '$points'");
                }
            } elseif (preg_match('/give\s*(\d+)/i', $line, $matches)) {
                $points = (int) $matches[1];
                if ($points < 0) {
                    throw new InvalidArgumentException("Points must be non-negative in line " . ($lineNumber + 1) . ": '$points'");
                }
                if ($hasGive) {
                    throw new InvalidArgumentException("Multiple default give statements detected at line " . ($lineNumber + 1) . ".");
                }
                $hasGive = true;
            } else {
                throw new InvalidArgumentException("Invalid PointsScript syntax in line " . ($lineNumber + 1) . ": '$line'");
            }
        }

        if (!$hasGive && empty($countConditions)) {
            throw new InvalidArgumentException("No default give statement found in PointsScript, and no conditions cover all cases.");
        }

        return true;
    }

    private function validateCondition(string $condition, array &$countConditions): bool
    {
        if (preg_match('/count\(options\s*([=<>!]+)\s*(\d+)\)/i', $condition, $matches)) {
            $operator = $matches[1];
            $value = (int) $matches[2];

            if (!in_array($operator, ['==', '>=', '<=', '>', '<', '!='])) {
                return false;
            }

            if ($value < 0) {
                return false;
            }

            foreach ($countConditions as $prev) {
                if ($this->isCountOverlap($prev['operator'], $prev['value'], $operator, $value)) {
                    throw new InvalidArgumentException("Condition '$condition' overlaps with previous condition 'count(options {$prev['operator']} {$prev['value']})', making later rules unreachable.");
                }
            }
            $countConditions[] = ['operator' => $operator, 'value' => $value];
            return true;
        }

        if (preg_match('/(!)?contains\("([^"]*)"\)/i', $condition, $matches)) {
            $substring = $matches[2];
            if (empty($substring)) {
                return false;
            }
            return true;
        }

        return false;
    }

    private function isCountOverlap(string $prevOperator, int $prevValue, string $operator, int $value): bool
    {
        if ($prevOperator === '>=' && $operator === '>=' && $value >= $prevValue) {
            return true;
        }
        if ($prevOperator === '==' && $operator === '==' && $value === $prevValue) {
            return true;
        }
        return false;
    }
}

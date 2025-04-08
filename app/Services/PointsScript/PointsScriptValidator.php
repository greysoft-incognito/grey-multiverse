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
        $lengthConditions = [];
        $matchesConditions = [];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/if\s*\((.*?)\)\s*give\s*(\d+)/i', $line, $matches)) {
                $condition = trim($matches[1]);
                $points = (int) $matches[2];

                if (!$this->validateCondition($condition, $countConditions, $lengthConditions, $matchesConditions)) {
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

        if (!$hasGive && empty($countConditions) && empty($lengthConditions) && empty($matchesConditions)) {
            throw new InvalidArgumentException("No default give statement found in PointsScript, and no conditions cover all cases.");
        }

        return true;
    }

    private function validateCondition(string $condition, array &$countConditions, array &$lengthConditions, array &$matchesConditions): bool
    {
        $orParts = preg_split('/\s+or\s+/i', $condition);
        foreach ($orParts as $orPart) {
            $andParts = preg_split('/\s+and\s+/i', trim($orPart));
            foreach ($andParts as $expression) {
                if (!$this->validateExpression(trim($expression), $countConditions, $lengthConditions, $matchesConditions)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function validateExpression(string $expression, array &$countConditions, array &$lengthConditions, array &$matchesConditions): bool
    {
        if (preg_match('/count\(options\s*([=<>!]+)\s*(\d+)\)/i', $expression, $matches)) {
            $operator = $matches[1];
            $value = (int) $matches[2];
            return $this->validateComparison($operator, $value, $countConditions, "count");
        }

        if (preg_match('/(!)?length\(([=<>!]+)\s*(\d+)\)/i', $expression, $matches)) {
            $operator = $matches[2];
            $value = (int) $matches[3];
            return $this->validateComparison($operator, $value, $lengthConditions, "length");
        }

        if (preg_match('/(!)?contains\("([^"]*)"\)/i', $expression, $matches)) {
            $substring = $matches[2];
            return !empty($substring);
        }

        if (preg_match('/(!)?equals\("([^"]*)"\)/i', $expression, $matches)) {
            $value = $matches[2];
            return !empty($value);
        }

        if (preg_match('/matches\((.*?),\s*([=<>!]+)\s*(\d+)\)/i', $expression, $matches)) {
            $valuesString = trim($matches[1]);
            $operator = $matches[2];
            $value = (int) $matches[3];
            preg_match_all('/"([^"]*)"/', $valuesString, $valueMatches);
            $values = $valueMatches[1];
            if (empty($values)) {
                return false;
            }
            return $this->validateComparison($operator, $value, $matchesConditions, "matches", count($values));
        }

        if (preg_match('/missing\(\)/i', $expression)) {
            return true;
        }

        if (preg_match('/nothing\(\)/i', $expression)) {
            return true;
        }

        return false;
    }

    private function validateComparison(string $operator, int $value, array &$conditions, string $type, ?int $maxValue = null): bool
    {
        if (!in_array($operator, ['==', '>=', '<=', '>', '<', '!='])) {
            return false;
        }

        if ($value < 0 || ($maxValue !== null && $value > $maxValue)) {
            return false;
        }

        foreach ($conditions as $prev) {
            if ($this->isOverlap($prev['operator'], $prev['value'], $operator, $value)) {
                throw new InvalidArgumentException("Condition '$type($operator $value)' overlaps with previous condition '$type({$prev['operator']} {$prev['value']})', making later rules unreachable.");
            }
        }
        $conditions[] = ['operator' => $operator, 'value' => $value];
        return true;
    }

    private function isOverlap(string $prevOperator, int $prevValue, string $operator, int $value): bool
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

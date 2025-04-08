<?php

namespace App\Services\PointsScript;

class PointsScriptParser
{
    /**
     * Evaluate a PointsScript string against an input value.
     *
     * @param string $pointsScript The PointsScript to evaluate
     * @param mixed $input The input data (string or array)
     * @return int The calculated points
     */
    public function evaluate(string $pointsScript, $input): int
    {
        if (empty(trim($pointsScript))) {
            return 0;
        }

        $lines = explode("\n", trim($pointsScript));
        $defaultPoints = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/if\s*\((.*?)\)\s*give\s*(\d+)/i', $line, $matches)) {
                $condition = trim($matches[1]);
                $points = (int) $matches[2];

                if ($this->evaluateCondition($condition, $input)) {
                    return $points;
                }
            } elseif (preg_match('/give\s*(\d+)/i', $line, $matches)) {
                $defaultPoints = (int) $matches[1];
            }
        }

        return $defaultPoints;
    }

    /**
     * Evaluate a condition against the input.
     *
     * @param string $condition The condition to check
     * @param mixed $input The input data
     * @return bool Whether the condition is true
     */
    private function evaluateCondition(string $condition, $input): bool
    {
        // Split by 'or' first (lower precedence)
        $orParts = preg_split('/\s+or\s+/i', $condition);
        foreach ($orParts as $orPart) {
            $andParts = preg_split('/\s+and\s+/i', trim($orPart));
            $andResult = true;
            foreach ($andParts as $expression) {
                $expression = trim($expression);
                if (!$this->evaluateExpression($expression, $input)) {
                    $andResult = false;
                    break;
                }
            }
            if ($andResult) {
                return true; // Short-circuit OR
            }
        }
        return false;
    }

    private function evaluateExpression(string $expression, $input): bool
    {
        if (preg_match('/count\(options\s*([=<>!]+)\s*(\d+)\)/i', $expression, $matches)) {
            $operator = $matches[1];
            $value = (int) $matches[2];
            $count = is_array($input) ? count($input) : 0;
            return $this->compare($count, $operator, $value);
        }

        if (preg_match('/(!)?length\(([=<>!]+)\s*(\d+)\)/i', $expression, $matches)) {
            $negated = !empty($matches[1]);
            $operator = $matches[2];
            $value = (int) $matches[3];
            $length = is_string($input) ? strlen($input) : (is_array($input) ? count($input) : 0);
            $result = $this->compare($length, $operator, $value);
            return $negated ? !$result : $result;
        }

        if (preg_match('/(!)?contains\("([^"]*)"\)/i', $expression, $matches)) {
            $negated = !empty($matches[1]);
            $substring = $matches[2];
            if (is_string($input)) {
                $contains = stripos($input, $substring) !== false;
                return $negated ? !$contains : $contains;
            }
        }

        if (preg_match('/(!)?equals\("([^"]*)"\)/i', $expression, $matches)) {
            $negated = !empty($matches[1]);
            $value = $matches[2];
            $equals = is_scalar($input) ? (string) $input === $value : false;
            return $negated ? !$equals : $equals;
        }

        if (preg_match('/matches\((.*?),\s*([=<>!]+)\s*(\d+)\)/i', $expression, $matches)) {
            $valuesString = trim($matches[1]);
            $operator = $matches[2];
            $value = (int) $matches[3];
            preg_match_all('/"([^"]*)"/', $valuesString, $valueMatches);
            $values = $valueMatches[1];
            $matchCount = $this->countMatches($input, $values);
            return $this->compare($matchCount, $operator, $value);
        }

        if (preg_match('/missing\(\)/i', $expression)) {
            return $input === null;
        }

        if (preg_match('/nothing\(\)/i', $expression)) {
            return ($input === "" || (is_array($input) && empty($input)));
        }

        return false;
    }

    private function countMatches($input, array $values): int
    {
        if (is_string($input)) {
            return in_array($input, $values) ? 1 : 0;
        }
        if (is_array($input)) {
            $count = 0;
            foreach ($values as $value) {
                if (in_array($value, $input)) {
                    $count++;
                }
            }
            return $count;
        }
        return 0;
    }

    private function compare(int $left, string $operator, int $right): bool
    {
        return match ($operator) {
            '==' => $left == $right,
            '>=' => $left >= $right,
            '<=' => $left <= $right,
            '>' => $left > $right,
            '<' => $left < $right,
            '!=' => $left != $right,
            default => false,
        };
    }
}

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
        if (preg_match('/count\(options\s*([=<>!]+)\s*(\d+)\)/i', $condition, $matches)) {
            $operator = $matches[1];
            $value = (int) $matches[2];
            $count = is_array($input) ? count($input) : 0;

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

        if (preg_match('/(!)?contains\("([^"]*)"\)/i', $condition, $matches)) {
            $negated = !empty($matches[1]);
            $substring = $matches[2];

            if (is_string($input)) {
                $contains = stripos($input, $substring) !== false;
                return $negated ? !$contains : $contains;
            }
        }

        return false;
    }
}

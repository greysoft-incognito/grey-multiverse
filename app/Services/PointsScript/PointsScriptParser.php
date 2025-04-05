<?php

namespace App\Services\PointsScript;

class PointsScriptParser
{
    public function evaluate(string $points_script, $userAnswer): int
    {
        if (empty($points_script)) {
            return 0;
        }

        $lines = explode("\n", trim($points_script));
        $defaultPoints = 0;
        $conditionMet = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Match if or else if statements
            if (preg_match('/(else\s+)?if\s*\((.*?)\)\s*return\s*(\d+)/i', $line, $matches)) {
                $isElse = !empty($matches[1]);
                $condition = trim($matches[2]);
                $points = (int) $matches[3];

                // Skip if an earlier condition was met and this is an else if
                if ($isElse && $conditionMet) {
                    continue;
                }

                if ($this->evaluateCondition($condition, $userAnswer)) {
                    $conditionMet = true;
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

    private function evaluateCondition(string $condition, $userAnswer): bool
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

        // Handle contains() and !contains() conditions
        if (preg_match('/(!)?contains\("([^"]*)"\)/i', $condition, $matches)) {
            $negated = !empty($matches[1]); // True if !contains
            $substring = $matches[2];

            if (is_string($userAnswer)) {
                $contains = stripos($userAnswer, $substring) !== false;
                return $negated ? !$contains : $contains;
            }
        }

        return false;
    }
}

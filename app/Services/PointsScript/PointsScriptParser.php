<?php

namespace App\Services\PointsScript;

class PointsScriptParser
{
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

    public function getMaxAnswer(string $pointsScript, string $valueType, array $options = []): array|string
    {
        if (empty(trim($pointsScript))) {
            return $valueType === 'array' ? [] : '';
        }

        $maxCondition = $this->getMaxCondition($pointsScript);
        if ($maxCondition === null || $maxCondition['condition'] === null) {
            return $valueType === 'array' ? [] : '';
        }

        return $this->generateInputForCondition($maxCondition['condition'], $valueType, $options);
    }

    private function getMaxCondition(string $pointsScript): ?array
    {
        $lines = explode("\n", trim($pointsScript));
        $maxPoints = 0;
        $maxCondition = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/if\s*\((.*?)\)\s*give\s*(\d+)/i', $line, $matches)) {
                $condition = trim($matches[1]);
                $points = (int) $matches[2];
                if ($points > $maxPoints) {
                    $maxPoints = $points;
                    $maxCondition = ['condition' => $condition, 'points' => $points];
                }
            } elseif (preg_match('/give\s*(\d+)/i', $line, $matches)) {
                $points = (int) $matches[1];
                if ($points > $maxPoints) {
                    $maxPoints = $points;
                    $maxCondition = ['condition' => null, 'points' => $points];
                }
            }
        }

        return $maxCondition;
    }

    private function generateInputForCondition(string $condition, string $valueType, array $options): array|string
    {
        $orParts = preg_split('/\s+or\s+/i', $condition);
        $andParts = preg_split('/\s+and\s+/i', trim($orParts[0])); // First OR clause

        return $valueType === 'array'
            ? $this->generateArrayInputForAndConditions($andParts, $options)
            : $this->generateStringInputForAndConditions($andParts);
    }

    private function generateArrayInputForAndConditions(array $andParts, array $options): array
    {
        $input = array_column($options, 'value');

        foreach ($andParts as $expression) {
            $expression = trim($expression);
            if (preg_match('/matches\((.*?),\s*([=<>!]+)\s*(\d+)\)/i', $expression, $match)) {
                preg_match_all('/"([^"]*)"/', $match[1], $matches);
                $operator = $match[2];
                $count = (int) $match[3];
                $values = $matches[1];
                $currentMatches = $this->countMatches($input, $values);
                $additional = $this->ensureMatchCount($input, $values, $operator, $count, $currentMatches);
                $input = array_merge($input, $additional);
            }
            if (preg_match('/count\(options\s*([=<>!]+)\s*(\d+)\)/i', $expression, $match)) {
                $operator = $match[1];
                $count = (int) $match[2];
                $input = $this->ensureCount($input, $operator, $count);
            }
        }

        return $input; // No array_unique to preserve duplicates
    }

    private function generateStringInputForAndConditions(array $andParts): string
    {
        $substrings = [];
        $minLength = 0;

        foreach ($andParts as $expression) {
            $expression = trim($expression);
            if (preg_match('/contains\("([^"]*)"\)/i', $expression, $matches)) {
                $substrings[] = $matches[1];
            } elseif (preg_match('/equals\("([^"]*)"\)/i', $expression, $matches)) {
                return $matches[1]; // equals trumps all
            } elseif (preg_match('/length\(([><=]+)\s*(\d+)\)/i', $expression, $matches)) {
                $operator = $matches[1];
                $value = (int) $matches[2];
                $minLength = $this->adjustMinLength($minLength, $operator, $value);
            }
        }

        $baseString = implode('', array_unique($substrings));
        if (strlen($baseString) < $minLength) {
            $padding = $minLength - strlen($baseString);
            $baseString .= str_repeat('x', $padding);
        }

        return $baseString;
    }

    private function ensureMatchCount(array $input, array $values, string $operator, int $count, int $currentMatches): array
    {
        $neededMatches = $count - $currentMatches;
        if ($neededMatches <= 0 || !in_array($operator, ['>=', '==', '>'])) {
            return [];
        }

        $additional = [];
        $availableValues = array_unique($values);
        $matchedValues = [];
        // Determine which values are already matched in the input
        foreach ($input as $item) {
            if (in_array($item, $values) && !in_array($item, $matchedValues)) {
                $matchedValues[] = $item;
            }
        }

        for ($i = 0; $i < $neededMatches; $i++) {
            $valueToAdd = null;
            $currentMatched = $matchedValues;
            foreach ($additional as $added) {
                if (in_array($added, $values) && !in_array($added, $currentMatched)) {
                    $currentMatched[] = $added;
                }
            }

            // Find a value that hasn't been matched yet
            foreach ($availableValues as $value) {
                if (!in_array($value, $currentMatched)) {
                    $valueToAdd = $value;
                    break;
                }
            }

            $valueToAdd = $valueToAdd ?? $availableValues[$i % count($availableValues)];
            $additional[] = $valueToAdd;
        }

        return $additional;
    }

    private function ensureCount(array $input, string $operator, int $count): array
    {
        $currentCount = count($input);
        $needed = $count - $currentCount;

        if ($needed <= 0 || !in_array($operator, ['>=', '==', '>'])) {
            return $input;
        }

        return array_merge($input, array_fill(0, $needed, 'x'));
    }

    private function adjustMinLength(int $currentMin, string $operator, int $value): int
    {
        return match ($operator) {
            '>' => max($currentMin, $value + 1),
            '>=' => max($currentMin, $value),
            '<', '<=' => $currentMin,
            '==' => max($currentMin, $value),
            default => $currentMin,
        };
    }

    private function evaluateCondition(string $condition, $input): bool
    {
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
                return true;
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
            $matched = [];
            foreach ($input as $item) {
                if (in_array($item, $values) && !in_array($item, $matched)) {
                    $count++;
                    $matched[] = $item;
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

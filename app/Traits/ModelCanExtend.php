<?php

namespace App\Traits;

use Valorin\Random\Random;

trait ModelCanExtend
{
    /**
     * Generate a Username for the user.
     *
     * @param  array{class-string, string}  $additional
     * @return string
     */
    protected static function generateUsername(
        string $string,
        string $field = 'username',
        string $separator = '_',
        array $additional = []
    ) {
        $username = str($string)->slug($separator);

        if (static::where($field, $username)->exists()) {
            $username = $username->append($separator)->append(Random::number(1111, 9999));
        }

        if (
            isset($additional[0]) &&
            method_exists($additional[0], 'where') &&
            static::where($additional[0], $additional[1] ?? $field)->exists()
        ) {
            $username = $username->append($separator)->append(Random::number(1111, 9999));
        }

        return $username->toString();
    }
}

<?php

namespace App\Helpers;

class Strings
{
    /**
     * Validates a JSON string.
     *
     * @param  string  $json The JSON string to validate.
     * @param  int  $depth Maximum depth. Must be greater than zero.
     * @param  int  $flags Bitmask of JSON decode options.
     * @return bool Returns true if the string is a valid JSON, otherwise false.
     */
    public static function jsonValidate($json, $depth = 512, $flags = 0)
    {
        if (function_exists('json_validate')) {
            return json_validate($json, $depth, $flags);
        }

        if (! is_string($json)) {
            return false;
        }

        try {
            json_decode($json, false, $depth, $flags | JSON_THROW_ON_ERROR);

            return true;
        } catch (\JsonException $e) {
            return false;
        }
    }

    /**
     * Format bytes to kilobytes, megabytes, gigabytes
     */
    public static function formatBytes(int $bytes, int $precision = 2, bool $bin = false): string
    {
        $units = $bin
            ? ['B', 'KiB', 'MiB', 'GiB', 'TiB']
            : ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log($bin ? 1024 : 1000));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).$units[$pow];
    }
}

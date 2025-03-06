<?php

namespace App\Services\Termii;

class Termii
{
    private static ?TermiiConnector $connector = null;

    public static function initialize(
        string $apiKey,
        string $baseUrl = 'https://v3.api.termii.com'
    ): TermiiConnector {
        if (self::$connector === null) {
            self::$connector = new TermiiConnector($apiKey, $baseUrl);
        }

        return self::$connector;
    }

    public function tokenApi(): self
    {
        return $this;
    }
}

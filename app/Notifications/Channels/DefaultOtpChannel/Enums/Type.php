<?php

namespace App\Notifications\Channels\DefaultOtpChannel\Enums;

use Valorin\Random\Random;

/**
 * HTTP Status codes.
 */
enum Type: string
{
    case ALPHANUM = 'alpha_numeric';
    case NUMERIC = 'numeric';

    /**
     * Get the sms provider
     */
    public function getCode($length = 6): string|int
    {
        $codes = [
            'numeric' => Random::otp($length),
            'alpha_numeric' => Random::string($length, false, true, true, false),
        ];

        return $codes[$this->value];
    }
}

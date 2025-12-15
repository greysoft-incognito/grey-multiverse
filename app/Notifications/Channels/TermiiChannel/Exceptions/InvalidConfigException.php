<?php

declare(strict_types=1);

namespace App\Notifications\Channels\TermiiChannel\Exceptions;

class InvalidConfigException extends \Exception
{
    public static function missingConfig(): self
    {
        return new self('Missing config: You must set the `caller_id` or `sender_id`.');
    }

    public static function missingToken(): self
    {
        return new self('Missing config: The `api_token` is required to send a TermiiSMS Notification.');
    }
}

<?php

namespace App\Notifications\Channels\TermiiChannel\Exceptions;

use Exception;
use App\Notifications\Channels\TermiiChannel\TermiiMessage;

class CouldNotSendNotification extends Exception
{
    public static function respondedWithAnError(Exception $exception)
    {
        return new static(
            $exception->getMessage(),
            $exception->getCode(),
            $exception
        );
    }

    public static function invalidMessage()
    {
        return new static(
            'The toTermii() method only accepts an instances of ' . TermiiMessage::class
        );
    }

    public static function missingFrom(): self
    {
        return new static('Notification was not sent. Missing `from` number.');
    }

    public static function invalidReceiver(): self
    {
        return new static(
            'The notifiable did not have a receiving phone number. Add a routeNotificationForTermii or routeNotificationForSms
            method or a phone_number attribute to your notifiable.'
        );
    }
}

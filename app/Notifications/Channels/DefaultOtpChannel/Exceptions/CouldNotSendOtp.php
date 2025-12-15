<?php

namespace App\Notifications\Channels\DefaultOtpChannel\Exceptions;

use App\Notifications\Channels\DefaultOtpChannel\DefaultOtp;
use Exception;

class CouldNotSendOtp extends Exception
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
            'The toOtp() method only accepts an instances of '.DefaultOtp::class
        );
    }

    public static function invalidReceiver(): self
    {
        return new static(
            'The notifiable did not have a receiving phone number or email address. Add a routeNotificationForOtp or routeNotificationForSms or routeNotificationForMail
            method or one of phone_number, phone, email attribute to your notifiable.'
        );
    }
}

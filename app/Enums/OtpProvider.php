<?php

namespace App\Enums;

use App\Notifications\Channels\DefaultOtpChannel\DefaultChannel;
use App\Notifications\Channels\DefaultOtpChannel\DefaultOtp;
use App\Notifications\Channels\TermiiChannel\TermiiEmailToken;
use App\Notifications\Channels\TermiiChannel\TermiiOtpChannel;
use App\Notifications\Channels\TermiiChannel\TermiiToken;
use App\Notifications\Channels\TermiiChannel\TermiiVoiceMessage;
use App\Notifications\Channels\TermiiChannel\TermiiVoiceToken;

/**
 * HTTP Status codes.
 */
enum OtpProvider: string
{
    case DEFAULT = DefaultChannel::class;
    case TERMII_TOKEN = TermiiOtpChannel::class.'.token';
    case TERMII_VOICE = TermiiOtpChannel::class.'.voice';
    case TERMII_EMAIL_TOKEN = TermiiOtpChannel::class.'.email_token';
    case TERMII_VOICE_TOKEN = TermiiOtpChannel::class.'.voice_token';

    /**
     * Get the sms provider
     */
    public function getMessage(string $message = null): DefaultOtp|TermiiToken|TermiiVoiceMessage|TermiiEmailToken|TermiiVoiceToken
    {
        /** @var string<'TWILLIO'|'KUDISMS'|'TERMII'> $type */
        $type = $this->name;

        $classes = [
            'DEFAULT' => DefaultOtp::class,
            'TERMII_TOKEN' => TermiiToken::class,
            'TERMII_VOICE' => TermiiVoiceMessage::class,
            'TERMII_EMAIL_TOKEN' => TermiiEmailToken::class,
            'TERMII_VOICE_TOKEN' => TermiiVoiceToken::class,
        ];

        if (isset($classes[$type])) {
            return (new $classes[$type]())->message($message ?? '');
        }

        // Return The Default
        return (new DefaultOtp())->message($message ?? '');
    }

    /**
     * Get the sms provider
     */
    public static function getChannel(): string
    {
        $type = dbconfig('prefered_otp_channel', 'DEFAULT');

        foreach (self::cases() as $case) {
            if ($case->name === $type) {
                return str($case->value)->before('.')->toString();
            }
        }

        // Return The Default
        return self::DEFAULT->value;
    }

    public static function tryFromName(string $name): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }
}

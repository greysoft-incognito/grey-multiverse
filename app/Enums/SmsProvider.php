<?php

namespace App\Enums;

use App\Notifications\Channels\TermiiChannel\TermiiChannel;
use App\Notifications\Channels\TermiiChannel\TermiiSmsMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ToneflixCode\KudiSmsNotification\KudiSmsChannel;
use ToneflixCode\KudiSmsNotification\KudiSmsMessage;

/**
 * HTTP Status codes.
 */
enum SmsProvider: string
{
    case TWILLIO = TwilioChannel::class;
    case KUDISMS = KudiSmsChannel::class;
    case TERMII = TermiiChannel::class;

    /**
     * Get the sms provider
     */
    public static function getMessage(string $message): TwilioSmsMessage|KudiSmsMessage|TermiiSmsMessage
    {
        /** @var string<'TWILLIO'|'KUDISMS'|'TERMII'> $type */
        $type = dbconfig('prefered_sms_channel', 'TWILLIO');

        $classes = [
            'TWILLIO' => TwilioSmsMessage::class,
            'KUDISMS' => KudiSmsMessage::class,
            'TERMII' => TermiiSmsMessage::class,
        ];

        if (isset($classes[$type])) {
            return (new $classes[$type]())->message($message);
        }

        // Return Twillio as Default
        return (new TwilioSmsMessage())->content($message);
    }

    /**
     * Get the sms provider
     */
    public static function getChannel(): string
    {
        $type = dbconfig('prefered_sms_channel', 'TWILLIO');

        foreach (self::cases() as $case) {
            if ($case->name === $type) {
                return $case->value;
            }
        }

        // Return Twillio as Default
        return self::TWILLIO->value;
    }
}

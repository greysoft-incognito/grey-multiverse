<?php

namespace App\Notifications;

use App\Enums\SmsProvider;
use App\Helpers\Providers;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountVerified extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $type = 'email'
    ) {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $pref = collect(dbconfig('prefered_notification_channels', ['mail', 'sms']))->toArray();

        return in_array('sms', $pref) && in_array('mail', $pref)
            ? ['mail', SmsProvider::getChannel(), 'database']
            : (in_array('sms', $pref)
                ? [SmsProvider::getChannel(), 'database']
                : ['mail', 'database']
            );
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = Providers::messageParser(
            'send_verified',
            $notifiable,
            [
                'type' => $this->type,
                'label' => 'email address',
                'app_url' => config('app.frontend_url', dbconfig('app_url')),
                'app_name' => dbconfig('app_name'),
            ]
        );

        return (new MailMessage())
            ->subject($message->subject)
            ->view(['email', 'email-plain'], [
                'subject' => $message->subject,
                'lines' => $message->lines,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = Providers::messageParser(
            'send_verified::sms',
            $notifiable,
            [
                'type' => $this->type,
                'label' => 'phone number',
                'app_url' => config('app.frontend_url', config('app.url')),
                'app_name' => dbconfig('app_name'),
            ]
        );

        return [
            'title' => $message->subject,
            'message' => $message->plainBody,
            'important' => false,
        ];
    }

    /**
     * Get the sms representation of the notification.
     *
     * @param  mixed  $n  notifiable
     */
    public function toSms($n)
    {
        $n ??= $n->user ?? $n;

        $message = Providers::messageParser(
            'send_verified::sms',
            $n,
            [
                'type' => $this->type,
                'label' => 'phone number',
                'app_url' => config('app.frontend_url', config('app.url')),
                'app_name' => dbconfig('app_name'),
            ]
        );

        return SmsProvider::getMessage($message->plainBody);
    }

    public function toTwilio($n): \NotificationChannels\Twilio\TwilioSmsMessage
    {
        return $this->toSms($n);
    }

    public function toKudiSms($n): \ToneflixCode\KudiSmsNotification\KudiSmsMessage
    {
        return $this->toSms($n);
    }

    public function toTermii($n): \App\Notifications\Channels\TermiiChannel\TermiiMessage
    {
        return $this->toSms($n);
    }
}

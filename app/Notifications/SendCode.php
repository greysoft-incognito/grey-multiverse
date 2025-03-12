<?php

namespace App\Notifications;

use App\Enums\OtpProvider;
use App\Enums\SmsProvider;
use App\Helpers\Providers;
use App\Helpers\Url;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCode extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        public ?string $code = null,
        public string $type = 'reset',
        public ?string $token = null
    ) {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via($notifiable)
    {
        if (dbconfig('prefered_otp_channel', 'DEFAULT')) {
            return [OtpProvider::getChannel()];
        }

        $channels = str($this->type)->after('verify-')->is('phone')
            ? [SmsProvider::getChannel()]
            : (
                str($this->type)->is('verify')
                ? ['mail']
            : dbconfig('prefered_notification_channels', ['mail', 'sms'])
            );

        return collect($channels)->map(fn($ch) => $ch == 'sms' ? SmsProvider::getChannel() : $ch)->toArray();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $this->code ??= $notifiable->code;
        $this->token ??= $notifiable->token ?? Url::base64urlEncode($this->code . '|' . md5(time()));
        $notifiable = $notifiable->user ?? $notifiable;

        /** @var \Carbon\Carbon */
        $datetime = $notifiable->last_attempt;

        $dateAdd = $datetime?->addSeconds(dbconfig('token_lifespan', 30));

        return Providers::messageParser(
            "send_code::$this->type",
            $notifiable,
            [
                'type' => $this->type,
                'code' => $this->code,
                'token' => $this->token,
                'label' => 'email address',
                'app_url' => config('app.frontend_url', dbconfig('app_url')),
                'app_name' => dbconfig('app_name'),
                'duration' => $dateAdd->longAbsoluteDiffForHumans(),
            ]
        )->toMail();
    }

    /**
     * Get the sms representation of the notification.
     *
     * @param  mixed  $notifiable  notifiable
     */
    public function toSms($notifiable)
    {
        $this->code ??= $notifiable->code;
        $this->token ??= $notifiable->token ?? Url::base64urlEncode($this->code . '|' . md5(time()));
        $notifiable = $notifiable->user ?? $notifiable;

        /** @var \Carbon\Carbon */
        $datetime = $notifiable->last_attempt;

        $dateAdd = $datetime?->addSeconds(dbconfig('token_lifespan', 30));

        $message = Providers::messageParser(
            "send_code::$this->type",
            $notifiable,
            [
                'type' => $this->type,
                'code' => $this->code,
                'token' => $this->token,
                'label' => 'email address',
                'app_url' => config('app.frontend_url', dbconfig('app_url')),
                'app_name' => dbconfig('app_name'),
                'duration' => $dateAdd->longAbsoluteDiffForHumans(),
            ]
        );

        return SmsProvider::getMessage($message->toSms());
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

    public function toOtp(object $notifiable)
    {
        $this->code ??= $notifiable->code;

        $type = OtpProvider::tryFromName(dbconfig('prefered_otp_channel', 'DEFAULT'));

        if ($type) {
            return $type->getMessage($this->code);
        }
    }
}

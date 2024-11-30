<?php

namespace App\Notifications;

use App\Enums\SmsProvider;
use App\Helpers\Providers;
use App\Models\TempUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $type = 'mail') {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->type === 'sms' ? [SmsProvider::getChannel()] : ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User|TempUser $notifiable): MailMessage
    {
        $dateAdd = $notifiable->last_attempt?->addSeconds(Providers::config('token_lifespan', 30));

        return (new MailMessage())
            ->subject('One Time Password')
            ->view(['email', 'email-plain'], [
                'subject' => 'One Time Password',
                'lines' => [
                    "Hello $notifiable->firstname,",
                    "Your OTP is <strong>{$notifiable->otp}</strong>.",
                    "This OTP expires in {$dateAdd->longAbsoluteDiffForHumans()}"
                ],
            ]);
    }
    /**
     * Get the sms representation of the notification.
     *
     * @param  User|TempUser  $notifiable  notifiable
     */
    public function toSms(User|TempUser $notifiable)
    {
        $dateAdd = $notifiable->last_attempt?->addSeconds(Providers::config('token_lifespan', 30));

        $message = __('Your OTP is :0, it will expire in :1.', [
            $notifiable->otp,
            $dateAdd->longAbsoluteDiffForHumans(),
        ]);

        return SmsProvider::getMessage($message);
    }

    public function toTwilio($n): \NotificationChannels\Twilio\TwilioSmsMessage
    {
        return $this->toSms($n);
    }

    public function toKudiSms($n): \ToneflixCode\KudiSmsNotification\KudiSmsMessage
    {
        return $this->toSms($n);
    }
}

<?php

namespace App\Notifications;

use App\Enums\SmsProvider;
use App\Helpers\Providers;
use App\Models\FormData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;

class FormSubmitedSuccessfully extends Notification //implements ShouldQueue
{
    use Queueable;

    protected $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($notifiable->form->dont_notify) {
            return [];
        }

        $pref = collect(dbconfig('prefered_notification_channels', ['mail', 'sms']))->toArray();

        return in_array('sms', $pref) && in_array('mail', $pref)
            ? ['mail', SmsProvider::getChannel()]
            : (in_array('sms', $pref)
                ? [SmsProvider::getChannel()]
                : ['mail']
            );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(FormData $notifiable)
    {
        $submission = collect($notifiable->data)
            ->merge(['fullname' => $notifiable->fullname])
            ->filter(fn($v) => is_scalar($v))
            ->toArray();

        $message = Providers::messageParser(
            'form_submited',
            [
                'fullname' => $notifiable->fullname,
                'form_name' => $notifiable->form->name,
                'success_message' => __($notifiable->form->success_message, $submission),
                'qr_code' => route('form.data.qr', ['form', $notifiable->id]),
                'app_name' => dbconfig('app_name'),
            ]
        );

        return (new MailMessage())
            ->subject($message->subject)
            ->view(['email', 'email-plain'], [
                'subject' => $message->subject,
                'lines' => $message->lines,
            ]);
        // return $message->toMail();
    }

    /**
     * Get the sms representation of the notification.
     *
     * @param  FormData  $notifiable  notifiable
     */
    public function toSms(FormData $notifiable)
    {
        $submission = collect($notifiable->data)
            ->merge(['fullname' => $notifiable->fullname])
            ->filter(fn($v) => is_scalar($v))
            ->toArray();

        $message = Providers::messageParser(
            'form_submited',
            [
                'fullname' => $notifiable->fullname,
                'form_name' => $notifiable->form->name,
                'success_message' => __($notifiable->form->success_message, $submission),
                'qr_code' => route('form.data.qr', ['form', $notifiable->id]),
                'app_name' => dbconfig('app_name'),
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

<?php

namespace V1\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

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

        $pref = config('settings.prefered_notification_channels', ['mail', 'sms']);

        return in_array('sms', $pref) && in_array('mail', $pref)
            ? ['mail', TwilioChannel::class]
            : (in_array('sms', $pref)
                ? [TwilioChannel::class]
                : ['mail']);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($n)
    {
        $fname_field = $n->form->fields()->where('name', 'like', '%firstname%')->orWhere('name', 'like', '%first_name%')->first();
        $lname_field = $n->form->fields()->where('name', 'like', '%lastname%')->orWhere('name', 'like', '%last_name%')->first();
        $fullname_field = $n->form->fields()->where('name', 'like', '%fullname%')->orWhere('name', 'like', '%full_name%')->first();
        $name_field = $n->form->fields()->where('name', 'like', '%name%')->first();

        $this->name = collect([
            $fname_field ? $n->data[$fname_field->name] : '',
            $lname_field ? $n->data[$lname_field->name] : '',
            $fullname_field && ! $fname_field && ! $fname_field ? $n->data[$fullname_field->name] : '',
            $name_field && ! $fname_field && ! $fname_field && ! $fullname_field ? $n->data[$name_field->name] : '',
        ])->filter(fn ($name) => $name !== '')->implode(' ');

        $qr_code = $n->id;
        $message = [
            'name' => $this->name,
            'cta' => str($n->form->success_message)->contains(':qrcode') ? ['qrcode' => $qr_code] : [],
            'message_line1' => __(str($n->form->success_message)->remove(':qrcode', false)->toString(), [
                'fullname' => $this->name,
                'qrcode' => $qr_code,
                'form' => $n->form->title,
            ]),
            'message_line3' => 'If there are any further information, we will not hesitate to contact you.',
            'close_greeting' => __('Regards, <br/>:0', [$n->form->name]),
            'message_help' => 'Your QR Code may be required to be scanned at the reception desk.',
        ];

        return (new MailMessage)->view(
            ['email', 'email-plain'], $message
        )
            ->subject(__(':0 Form submission recieved', [$n->form->name]));
    }

    /**
     * Get the sms representation of the notification.
     *
     * @param  mixed  $n    notifiable
     * @return \NotificationChannels\Twilio\TwilioSmsMessage
     */
    public function toTwilio($n)
    {
        $fname_field = $n->form->fields()->where('name', 'like', '%firstname%')->orWhere('name', 'like', '%first_name%')->first();
        $lname_field = $n->form->fields()->where('name', 'like', '%lastname%')->orWhere('name', 'like', '%last_name%')->first();
        $fullname_field = $n->form->fields()->where('name', 'like', '%fullname%')->orWhere('name', 'like', '%full_name%')->first();
        $name_field = $n->form->fields()->where('name', 'like', '%name%')->first();

        $this->name = collect([
            $fname_field ? $n->data[$fname_field->name] : '',
            $lname_field ? $n->data[$lname_field->name] : '',
            $fullname_field && ! $fname_field && ! $fname_field ? $n->data[$fullname_field->name] : '',
            $name_field && ! $fname_field && ! $fname_field && ! $fullname_field ? $n->data[$name_field->name] : '',
        ])->filter(fn ($name) => $name !== '')->implode(' ');

        $qr_code = $n->id;
        $message = __($n->form->success_message, [
            'fullname' => $this->name,
            'qrcode' => $qr_code,
            'form' => $n->form->title,
        ]);

        $message = __('Hi :0, ', [$this->name]).$message;

        return (new TwilioSmsMessage())
            ->content($message);
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

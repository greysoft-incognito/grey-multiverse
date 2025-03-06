<?php

namespace App\Notifications\Channels\DefaultOtpChannel;

use App\Notifications\Channels\DefaultOtpChannel\Exceptions\CouldNotSendOtp;
use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class DefaultChannel
{
    /**
     * DefaultChannel constructor.
     */
    public function __construct(
        protected Dispatcher $events
    ) {}

    /**
     * Get the address to send a notification to.
     *
     * @param  \App\Models\User $notifiable
     * @param  Notification|null  $notification
     * @return mixed
     *
     * @throws CouldNotSendNotification
     */
    protected function getTo(object $notifiable, $notification = null)
    {
        if ($notifiable->routeNotificationFor(self::class, $notification)) {
            return $notifiable->routeNotificationFor(self::class, $notification);
        }
        if ($notifiable->routeNotificationFor('otp', $notification)) {
            return $notifiable->routeNotificationFor('otp', $notification);
        }
        if ($notifiable->routeNotificationFor('mail', $notification)) {
            return $notifiable->routeNotificationFor('mail', $notification);
        }
        if ($notifiable->routeNotificationFor('sms', $notification)) {
            return $notifiable->routeNotificationFor('sms', $notification);
        }
        if (isset($notifiable->email) || isset($notifiable->email_address)) {
            return $notifiable->email ?? $notifiable->email_address;
        }
        if (isset($notifiable->phone) || isset($notifiable->phone_number)) {
            return $notifiable->phone_number ?? $notifiable->phone;
        }

        throw CouldNotSendNotification::invalidReceiver();
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @return mixed
     *
     * @throws Exception
     */
    public function send(object $notifiable, Notification $notification)
    {
        try {
            $to = $this->getTo($notifiable, $notification);
            $to = is_array($to) ? array_key_first($to) : (string) $to;

            /** @var DefaultOtp $message */
            $message = $notification->toOtp($notifiable);

            if (!$message || is_string($message)) {
                $message = new DefaultOtp($message ?? '');
            }

            if (! $message instanceof DefaultOtp) {
                throw CouldNotSendOtp::invalidMessage();
            }

            $notifiable->last_attempt = now();
            $notifiable->otp = $message->type->getCode($message->pinLength);
            $notifiable->save();

            if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $dateAdd = $notifiable->last_attempt->addSeconds(dbconfig('token_lifespan', 30));

                Mail::send(
                    ['email', 'email-plain'],
                    [
                        'subject' => 'One Time Password',
                        'lines' => [
                            "Hello $notifiable->firstname,",
                            "Your OTP is <strong>{$notifiable->otp}</strong>.",
                            "This OTP expires in {$dateAdd->longAbsoluteDiffForHumans()}",
                        ],
                    ],
                    fn($message) => $message->to($to)->subject('One Time Password')
                );
            } else {
                $this->build($to, $notifiable);
            }
        } catch (\Exception $exception) {
            $event = new NotificationFailed(
                $notifiable,
                $notification,
                'defaultOtp',
                ['message' => $exception->getMessage(), 'exception' => $exception]
            );

            $this->events->dispatch($event);

            throw $exception;
        }
    }

    public function build(string $to, object $notifiable)
    {
        $dateAdd = $notifiable->last_attempt->addSeconds(dbconfig('token_lifespan', 30));
        $type = dbconfig('prefered_sms_channel', 'TWILLIO');
        $msg = "Your OTP is {$notifiable->otp}, it expires in {$dateAdd->longAbsoluteDiffForHumans()}";

        $classes = [
            'TWILLIO' => function () use ($to, $msg) {
                $client = new \Twilio\Rest\Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
                return $client->messages->create(
                    $to,
                    ['from' => env('TWILIO_FROM'), 'body' => $msg]
                );
            },
            'KUDISMS' => function () use ($to, $msg) {
                $instance = new \ToneflixCode\KudiSmsPhp\SmsSender(env('KUDISMS_SENDER_ID'), env('KUDISMS_API_KEY'));
                return $instance->send(
                    recipient: $to,
                    message: $msg
                );
            },
            'TERMII' => function ()  use ($to, $msg) {
                $termii = \Okolaa\TermiiPHP\Termii::initialize(config('termii-notification.api_key'));
                $message = new \Okolaa\TermiiPHP\Data\Message(
                    to: $to,
                    from: config('termii-notification.sender_id'),
                    sms: $msg,
                    type: "sms",
                    channel: \Okolaa\TermiiPHP\Enums\MessageChannel::Generic,
                );
                return $termii->messagingApi()->send($message);
            },
        ];

        return ($classes[$type] ?? $classes['TWILLIO'])();
    }
}

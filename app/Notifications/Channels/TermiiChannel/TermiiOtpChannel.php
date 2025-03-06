<?php

namespace App\Notifications\Channels\TermiiChannel;

use App\Notifications\Channels\TermiiChannel\Exceptions\CouldNotSendNotification;
use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;

class TermiiOtpChannel
{
    /**
     * TermiiChannel constructor.
     */
    public function __construct(
        protected TermiiNotification $termii,
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
        if ($notifiable->routeNotificationFor('sms', $notification)) {
            return $notifiable->routeNotificationFor('sms', $notification);
        }
        if ($notifiable->routeNotificationFor('otp', $notification)) {
            return $notifiable->routeNotificationFor('otp', $notification);
        }
        if ($notifiable->routeNotificationFor('mail', $notification)) {
            return $notifiable->routeNotificationFor('mail', $notification);
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

            /** @var \App\Notifications\Channels\TermiiChannel\TermiiMessage $message */
            $message = $notification->toOtp($notifiable);

            if (is_null($message) || is_string($message)) {
                $message = (new TermiiToken($message ?? ''));
            }

            if (! $message instanceof TermiiMessage) {
                throw CouldNotSendNotification::invalidMessage();
            }

            return $this->termii->sendMessage($message, $to);
        } catch (\Exception $exception) {

            $event = new NotificationFailed(
                $notifiable,
                $notification,
                'termii',
                ['message' => $exception->getMessage(), 'exception' => $exception]
            );

            $this->events->dispatch($event);

            throw $exception;
        }
    }
}

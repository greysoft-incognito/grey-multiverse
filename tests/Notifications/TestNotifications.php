<?php

namespace Tests\Notifications;

use Illuminate\Notifications\Notification;

class TestNotifications extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => 'Test Notification',
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Assumenda, neque.',
        ];
    }
}

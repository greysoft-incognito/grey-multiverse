<?php

namespace App\Listeners;

use App\Events\Verified;
use App\Notifications\AccountVerified;

class SendVerifiedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $event->user->notify(new AccountVerified($event->type));
    }
}

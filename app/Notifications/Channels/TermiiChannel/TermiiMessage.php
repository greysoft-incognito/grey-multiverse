<?php

namespace App\Notifications\Channels\TermiiChannel;

use Illuminate\Support\Traits\Macroable;

class TermiiMessage
{
    use Macroable;

    public string $message;

    public string $callerId;

    public string $senderId;

    public ?string $emailId;
}

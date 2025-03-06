<?php

namespace App\Notifications\Channels\TermiiChannel;

use Illuminate\Support\Traits\Macroable;

class TermiiSmsMessage extends TermiiMessage
{
    use Macroable;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $message = '',
        public string $senderId = '',
    ) {}

    /**
     * Set the message.
     *
     * @return $this
     */
    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set the message senderId.
     *
     * @return $this
     */
    public function senderId(string $senderId): self
    {
        $this->senderId = $senderId;

        return $this;
    }
}

<?php

namespace App\Notifications\Channels\TermiiChannel;

use Illuminate\Support\Traits\Macroable;

class TermiiToken extends TermiiMessage
{
    use Macroable;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $message = '',
        public string $senderId = '',
        public int $pinLength = 6,
        public int $pinAttempts = 3,
        public int $pinTimeToLiveMinute = 5,
    ) {}

    /**
     * Set the message.
     *
     * @return $this
     */
    public function message(string $message): self
    {
        $this->message = $message ?? "Your verification code is";

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

    /**
     * Set the pinLength.
     *
     * @return $this
     */
    public function pinLength(string $pinLength): self
    {
        $this->pinLength = $pinLength;

        return $this;
    }

    /**
     * Set the pinAttempts.
     *
     * @return $this
     */
    public function pinAttempts(string $pinAttempts): self
    {
        $this->pinAttempts = $pinAttempts;

        return $this;
    }

    /**
     * Set the pinTimeToLiveMinute.
     *
     * @return $this
     */
    public function pinTimeToLiveMinute(string $pinTimeToLiveMinute): self
    {
        $this->pinTimeToLiveMinute = $pinTimeToLiveMinute;

        return $this;
    }
}

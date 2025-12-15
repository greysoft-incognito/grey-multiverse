<?php

namespace App\Notifications\Channels\TermiiChannel;

use Illuminate\Support\Traits\Macroable;

class TermiiVoiceToken extends TermiiMessage
{
    use Macroable;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public int $pinLength = 6,
        public int $pinAttempts = 3,
        public int $pinTimeToLiveMinute = 5,
    ) {
    }

    /**
     * Set the message.
     *
     * @return $this
     */
    public function message(): self
    {
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

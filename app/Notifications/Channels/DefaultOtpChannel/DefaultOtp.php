<?php

namespace App\Notifications\Channels\DefaultOtpChannel;

use Illuminate\Support\Traits\Macroable;
use \App\Notifications\Channels\DefaultOtpChannel\Enums\Type;

class DefaultOtp
{
    use Macroable;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $message = '',
        public Type $type = Type::NUMERIC,
        public int $pinLength = 6,
        public int $pinAttempts = 3,
        public int $pinTimeToLiveMinute = 5,
    ) {}

    /**
     * Set the message.
     *
     * @return $this
     */
    public function message(?string $message = null): self
    {
        $message ??= "Your one time password is";

        $this->message = $message;

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

    /**
     * Set the pinTimeToLiveMinute.
     *
     * @return $this
     */
    public function type(Type $type): self
    {
        $this->type = $type;

        return $this;
    }
}

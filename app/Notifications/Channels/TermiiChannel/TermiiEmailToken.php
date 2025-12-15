<?php

namespace App\Notifications\Channels\TermiiChannel;

use Illuminate\Support\Traits\Macroable;

class TermiiEmailToken extends TermiiMessage
{
    use Macroable;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $message = '',
        public string $emailAddress = '',
        public string $emailConfigurationId = '',
    ) {
    }

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
     * Set the pinLenemailAddressgth.
     *
     * @return $this
     */
    public function emailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Set the emailConfigurationId.
     *
     * @return $this
     */
    public function emailConfigurationId(string $emailConfigurationId): self
    {
        $this->emailConfigurationId = $emailConfigurationId;

        return $this;
    }
}

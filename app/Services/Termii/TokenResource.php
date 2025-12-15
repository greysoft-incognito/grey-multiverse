<?php

namespace App\Services\Termii;

use Okolaa\TermiiPHP\Data\Token\InAppToken;
use Okolaa\TermiiPHP\Data\Token\SendToken;
use Okolaa\TermiiPHP\Data\Token\VoiceToken;

/**
 * Token allows businesses to generate, send, and verify one-time-passwords.
 */
class TokenResource
{
    public function email(string $emailAddress, string $code, string $emailConfigurationId)
    {
    }

    public function inApp(InAppToken $inAppToken)
    {
    }

    public function send(SendToken $sendToken)
    {
    }

    public function verify(string $pinId, string $pinCode)
    {
    }

    public function voiceCall(string $phoneNumber, string $code)
    {
    }

    public function voice(VoiceToken $voiceToken)
    {
    }
}

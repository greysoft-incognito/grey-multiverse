<?php

namespace App\Notifications\Channels\TermiiChannel;

use App\Notifications\Channels\TermiiChannel\Exceptions\CouldNotSendNotification;
use App\Notifications\Channels\TermiiChannel\Exceptions\InvalidConfigException;
use Okolaa\TermiiPHP\Data\Message;
use Okolaa\TermiiPHP\Data\Token\VoiceToken;
use Okolaa\TermiiPHP\Termii;

class TermiiNotification
{
    public ?string $senderId;

    public ?string $callerId;

    public ?string $emailId;

    public string $apiKey;

    public function __construct(
        string $senderId = null,
        string $apiKey = null,
        string $callerId = null,
        string $emailId = null,
    ) {
        $this->senderId = $senderId ?: config('termii-notification.sender_id');
        $this->callerId = $callerId ?: config('termii-notification.caller_id');
        $this->emailId = $emailId ?: config('termii-notification.email_id');
        $this->apiKey = $apiKey ?: config('termii-notification.api_key');

        if (! $this->apiKey) {
            throw InvalidConfigException::missingToken();
        }

        if (! $this->senderId && ! $this->callerId) {
            throw InvalidConfigException::missingConfig();
        }
    }

    public function sms(
        string $to,
        string $message,
        string $senderId = null,
        string $apiKey = null,
    ): \Saloon\Http\Response {
        if (empty($this->senderId)) {
            throw CouldNotSendNotification::missingFrom();
        }

        $termii = Termii::initialize(
            apiKey: $apiKey ?: $this->apiKey
        );

        $message = new Message(
            to: $to,
            from: $senderId ?: $this->senderId,
            sms: $message,
            type: 'sms',
            channel: \Okolaa\TermiiPHP\Enums\MessageChannel::Generic,
            mediaUrl: null,
            mediaCaption: null
        );

        return $termii->messagingApi()->send($message);
    }

    public function token(
        string $to,
        int $pinTimeToLiveMinute = 5,
        int $pinAttempts = 3,
        int $pinLength = 6,
        string $message = null,
        string $senderId = null,
        string $apiKey = null,
    ) {
        if (empty($this->senderId)) {
            throw CouldNotSendNotification::missingFrom();
        }

        $termii = Termii::initialize(
            apiKey: $apiKey ?: $this->apiKey
        );

        $message ??= 'Your verification code is';

        $tokenSend = new \Okolaa\TermiiPHP\Data\Token\SendToken(
            to: $to,
            from: $senderId ?: $this->senderId,
            messageText: $message.' <%pin%>',
            pinType: \Okolaa\TermiiPHP\Enums\PinType::Numeric,
            pinAttempts: $pinAttempts,
            pinTimeToLiveMinute: $pinTimeToLiveMinute,
            pinLength: $pinLength,
            pinPlaceHolder: '<%pin%>',
            channel: \Okolaa\TermiiPHP\Enums\TokenChannel::GENERIC,
            messageType: 'plain',
        );

        return $termii->tokenApi()->send($tokenSend);
    }

    public function voice(
        string $to,
        int $code = 6,
        string $apiKey = null,
    ) {
        $termii = Termii::initialize(
            apiKey: $apiKey ?: $this->apiKey
        );

        return $termii->tokenApi()->voiceCall(
            code: $code,
            phoneNumber: $to,
        );
    }

    public function emailToken(
        string $to,
        string $code,
        string $emailConfigurationId,
        string $apiKey = null,
    ) {
        $termii = Termii::initialize(
            apiKey: $apiKey ?: $this->apiKey
        );

        return $termii->tokenApi()->email(
            code: $code,
            emailAddress: $to,
            emailConfigurationId: $emailConfigurationId
        );
    }

    public function voiceToken(
        string $to,
        int $pinTimeToLiveMinute = 5,
        int $pinAttempts = 3,
        int $pinLength = 6,
        string $apiKey = null,
    ) {
        $termii = Termii::initialize(
            apiKey: $apiKey ?: $this->apiKey
        );

        $voiceToken = new VoiceToken(
            pinLength: $pinLength,
            phoneNumber: $to,
            pinAttempts: $pinAttempts,
            pinTimeToLiveMinute: $pinTimeToLiveMinute,
        );

        return $termii->tokenApi()->voice($voiceToken);
    }

    /**
     * Send a KudiMessage to the a phone number.
     *
     *
     * @return \Saloon\Http\Response
     *
     * @throws CouldNotSendNotification
     */
    public function sendMessage(TermiiMessage $message, ?string $to)//: \Saloon\Http\Response
    {
        if ($message instanceof TermiiSmsMessage) {
            return $this->sms(
                to: $to,
                message: $message->message,
                senderId: $message->senderId ?: $this->senderId,
            );
        }

        if ($message instanceof TermiiVoiceToken) {
            return $this->voiceToken(
                to: $to,
                pinLength: $message->pinLength,
                pinAttempts: $message->pinAttempts,
                pinTimeToLiveMinute: $message->pinTimeToLiveMinute,
            );
        }

        if ($message instanceof TermiiVoiceMessage) {
            return $this->voice(
                to: $to,
                code: $message->message,
            );
        }

        if ($message instanceof TermiiEmailToken) {
            return $this->emailToken(
                to: $to,
                code: $message->message,
                emailConfigurationId: $message->emailConfigurationId ?: $this->emailId,
            );
        }

        if ($message instanceof TermiiToken) {
            return $this->token(
                to: $to,
                pinTimeToLiveMinute: $message->pinTimeToLiveMinute,
                pinAttempts: $message->pinAttempts,
                pinLength: $message->pinLength,
                senderId: $message->senderId ?: $this->senderId,
                message: $message->message,
            );
        }

        throw CouldNotSendNotification::invalidMessage($message);
    }
}

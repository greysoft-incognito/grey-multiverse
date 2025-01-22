<?php

namespace App\Traits;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Conversationable
{
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'conversationable');
    }

    public function latestMessage()
    {
        return $this->messages()->whereNot('user_id', auth('sanctum')->id())->latest()->one();
    }

    public function sendMessage(string|User $user, string $text): Message
    {
        $message = $this->messages()->make();
        $message->user_id = is_string($user) ? $user : $user->id;
        $message->text = $text;
        $message->save();

        return $message;
    }
}

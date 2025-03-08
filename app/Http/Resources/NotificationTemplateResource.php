<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'sms' => $this->sms ?? $this->plain,
            'html' => $this->html,
            'args' => $this->args,
            'lines' => $this->lines ?? [],
            'plain' => $this->plain,
            'active' => $this->active,
            'subject' => $this->subject,
            'allowed' => $this->allowed,
            'footnote' => $this->footnote,
            'copyright' => $this->copyright,
        ];
    }
}

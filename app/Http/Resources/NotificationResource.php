<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use ToneflixCode\ResourceModifier\Services\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->data['title'],
            'message' => $this->data['message'],
            'readAt' => $this->read_at,
            'important' => $this->data['important'] ?? false,
            'createdAt' => $this->created_at,
        ];

        if ($request->boolean('premark')) {
            $this->markAsRead();
        }

        return $data;
    }
}

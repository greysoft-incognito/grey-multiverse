<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use ToneflixCode\ResourceModifier\Services\Json\JsonResource;

class LogResource extends JsonResource
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
            'type' => str($this->loggable_type)->afterLast('\\')->append(' ' . $this->loggable_id),
            'action' => $this->action,
            'ip' => $this->properties['ip'] ?? '',
            'description' => $this->description,
            'user' => $this->when($this->user, fn() => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'name' => $this->user->fullname,
            ]),
            'loggable' => $this->when($this->loggable, fn() => [
                'id' => $this->loggable_id,
                'type' => str($this->loggable_type)->afterLast('\\')->slug(),
                'target' => $this->loggable->name ?? $this->loggable->title ?? $this->loggable->key ?? '',
            ]),
            'createdAt' => $this->created_at,
        ];
    }
}

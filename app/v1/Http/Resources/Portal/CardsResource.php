<?php

namespace V1\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;

class CardsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'title' => $this->title,
            'content' => $this->content,
            'icon' => $this->icon,
            'color' => $this->color,
            'badge_text' => $this->badge_text,
            'sticker_text' => $this->sticker_text,
            'component' => $this->component,
            'rating' => $this->rating ?? 0,
            'price' => $this->price ?? 0,
            'image' => $this->images['image'],
            'link' => $this->link,
            'infos' => $this->infos,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

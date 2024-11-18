<?php

namespace V1\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;

class SlidersResource extends JsonResource
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
            'subtitle' => $this->subtitle,
            'content' => $this->content,
            'image' => $this->images['image'],
            'component' => $this->component,
            'link' => $this->link,
            'list' => $this->list,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

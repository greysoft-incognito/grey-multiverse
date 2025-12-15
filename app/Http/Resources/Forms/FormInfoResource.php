<?php

namespace App\Http\Resources\Forms;

use ToneflixCode\ResourceModifier\Services\Json\JsonResource;

class FormInfoResource extends JsonResource
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
            'formId' => $this->form_id,
            'priority' => $this->priority,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'content' => $this->content,
            'list' => $this->list ?: [],
            'icon' => $this->icon,
            'iconColor' => $this->icon_color,
            'incrementIcon' => $this->increment_icon,
            'imageUrl' => $this->image_url,
            'position' => $this->position,
            'type' => $this->type,
            'template' => $this->template,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

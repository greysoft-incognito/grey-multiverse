<?php

namespace V1\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;

class LearningPathResource extends JsonResource
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
            'description' => $this->description,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image' => $this->files['image'],
            'video' => $this->files['video'],
            'background' => $this->files['background'],
            'component' => $this->component,
            'video_link' => $this->video_link,
            'price' => $this->price,
        ];
    }
}

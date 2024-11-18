<?php

namespace V1\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $canShowSections = $request->route()->named('portals.pages.show') ||
            $request->route()->named('portals.pages.show.index');

        return [
            'id' => $this->id,
            'portal_page_id' => $this->portal_page_id,
            'title' => $this->title,
            'title_highlight' => $this->title_highlight,
            'subtitle' => $this->subtitle,
            'minititle' => $this->minititle,
            'content' => $this->content,
            'type' => $this->type,
            'image' => $this->images['image'],
            'image2' => $this->when($this->image2, $this->images['image2']),
            'background' => $this->when($this->background, $this->images['background']),
            'image_position' => $this->image_position,
            'component' => $this->component,
            'link' => $this->link,
            'list' => $this->list,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'cards' => $this->when($canShowSections && $this->type === 'cards', new CardsCollection($this->cards)),
            'sliders' => $this->when($canShowSections, new SlidersCollection($this->sliders)),
        ];
    }
}

<?php

namespace V1\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;
use V1\Services\AppInfo;

class PortalPageResource extends JsonResource
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
            'portal_id' => $this->portal_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'exerpt' => str($this->content)->words(15),
            'meta' => $this->meta,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'component' => $this->component,
            'footer_group' => $this->footer_group,
            'index' => $this->index,
            'image' => $this->images['image'],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sections' => $this->when($canShowSections, new SectionCollection($this->sections)),
            'portal' => $this->when($canShowSections, new PortalResource($this->portal)),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return AppInfo::api();
    }
}

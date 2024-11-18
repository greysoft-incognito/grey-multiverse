<?php

namespace V1\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;
use V1\Http\Resources\User\UserResource;

class BlogResource extends JsonResource
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
            'portal_id' => $this->portal_id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'slug' => $this->slug,
            'content' => $this->content,
            'exerpt' => str($this->content)->words(15),
            'image' => $this->images['image'],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->when($request->route()->named('portals.blogs.show') && $this->user, new UserResource($this->user)),
            'portal' => $this->when($request->route()->named('portals.blogs.show'), new PortalResource($this->portal)),
        ];
    }
}

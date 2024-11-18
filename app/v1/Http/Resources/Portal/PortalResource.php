<?php

namespace V1\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;
use V1\Http\Resources\FormResource;

class PortalResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'footer_info' => $this->footer_info,
            'meta' => $this->meta,
            'allow_registration' => $this->allow_registration,
            'reg_link_title' => $this->reg_link_title,
            'reg_fee' => $this->reg_fee,
            'reg_form_id' => $this->reg_form_id,
            'login_link_title' => $this->login_link_title,
            'logo' => $this->images['logo'],
            'favicon' => $this->images['favicon'],
            'banner' => $this->images['banner'],
            'copyright' => $this->copyright,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'socials' => $this->socials,
            'footer_groups' => $this->footer_groups,
            'footer_pages' => $this->footer_pages,
            'navbar_pages' => $this->navbar_pages,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'learning_paths' => $this->when($this->regForm->learningPaths ?? null, new LearningPathCollection($this->regForm->learningPaths ?? null)),
            'reg_form' => $this->when($request->route()->named('portals.show') && $this->regForm, new FormResource($this->regForm)),
        ];
    }
}

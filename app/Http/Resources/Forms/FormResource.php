<?php

namespace App\Http\Resources\Forms;

use ToneflixCode\ResourceModifier\Services\Json\JsonResource;
use V1\Http\Resources\Portal\LearningPathCollection;
use V1\Services\AppInfo;

class FormResource extends JsonResource
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
            'title' => $this->title,
            'external_link' => $this->external_link,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'banner_title' => $this->banner_title,
            'banner_info' => $this->banner_info,
            'template' => $this->template,
            'data_emails' => $this->data_emails->filter(fn($e) => $e != ''),
            'dont_notify' => $this->dont_notify,
            'socials' => $this->socials,
            'deadline' => $this->deadline,
            'require_auth' => $this->require_auth,
            'infos' => new FormInfoCollection($this->infos),
            'fields' => new FormFieldCollection($this->fields),
            'successMessage' => $this->success_message,
            'failureMessage' => $this->failure_message,
            'learning_paths' => $this->when(
                (bool) $this->learningPaths && ! $request->route()->named('home.forms.index'),
                new LearningPathCollection($this->learningPaths)
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
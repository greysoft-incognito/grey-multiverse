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
        $with = str($request->string('with'))->remove(' ')->explode(',');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'title' => $this->title,
            'syncing' => $this->syncing,
            'external_link' => $this->external_link,
            'logo' => $this->files['logo'],
            'banner' => $this->files['banner'],
            'banner_title' => $this->banner_title,
            'banner_info' => $this->banner_info,
            'template' => $this->template,
            'total_points' => $this->total_points,
            'data_emails' => $this->data_emails->filter(fn ($e) => $e != ''),
            'dont_notify' => $this->dont_notify,
            'socials' => $this->socials,
            'deadline' => $this->deadline?->format('Y/m/d H:i:s'),
            'require_auth' => $this->require_auth,
            'infos' => new FormInfoCollection($this->infos),
            'fields' => new FormFieldCollection($this->fields),
            'success_message' => $this->success_message,
            'failure_message' => $this->failure_message,
            'approval_message' => $this->approval_message,
            'rejection_message' => $this->rejection_message,
            'learning_paths' => $this->when(
                (bool) $this->learningPaths && ! $request->route()->named('home.forms.index'),
                new LearningPathCollection($this->learningPaths)
            ),
            'config' => $this->when($with->contains('config'), $this->config),
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

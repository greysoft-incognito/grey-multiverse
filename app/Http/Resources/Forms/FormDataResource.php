<?php

namespace App\Http\Resources\Forms;

use ToneflixCode\ResourceModifier\Services\Json\JsonResource;
use V1\Services\AppInfo;

class FormDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $form = $this->form;
        $data = $this->data;

        $name = str($this->name)->explode(' ');

        return collect([
            'id' => $this->id,
            'name' => $this->name,
            'firstname' => $name->first(),
            'lastname' => $name->count() > 1 ? $name->last() : '',
            'form_id' => $this->form_id,
            'email' => $this->whenNotNull($this->email),
            'phone' => $this->whenNotNull($this->phone),
            'qr' => route('form.data.qr', ['form', $this->id]),
            'scan_date' => $form->scan_date,
            'fields' => $form->fields,
            'status' => $this->status,
        ])
            ->merge($data)->except(['fields']);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return AppInfo::with(['fields' => new FormFieldCollection($this->form->fields)]);
    }
}

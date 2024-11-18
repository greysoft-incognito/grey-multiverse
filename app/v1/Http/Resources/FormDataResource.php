<?php

namespace V1\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
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

        $email_field = $form->fields()->email()->first();
        $phone_field = $form->fields()->phone()->first();
        $fname_field = $form->fields()->fname()->first();
        $lname_field = $form->fields()->lname()->first();
        $fullname_field = $form->fields()->fullname()->first();

        $name = collect([
            $this->data[$fname_field->name ?? '--'] ?? '',
            $this->data[$lname_field->name ?? '--'] ?? '',
            ! $fname_field && ! $lname_field ? $this->data[$fullname_field->name ?? $email_field->name ?? '--'] : '',
        ])->filter(fn ($name) => $name !== '')->implode(' ');

        return collect([
            'id' => $this->id,
            'name' => $name,
            'form_id' => $this->form_id,
            'email' => $this->whenNotNull($data[$email_field->name ?? ''] ?? null),
            'phone' => $this->whenNotNull($data[$phone_field->name ?? ''] ?? null),
            'qr' => route('form.data.qr', ['form', $this->id]),
            'scan_date' => $form->scan_date,
            'fields' => $form->fields,
        ])
            ->merge($this->data)->except(['fields']);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return AppInfo::with(['fields' => $this->form->fields]);
    }
}

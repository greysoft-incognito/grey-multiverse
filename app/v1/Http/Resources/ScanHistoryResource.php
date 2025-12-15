<?php

namespace V1\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use V1\Services\AppInfo;

class ScanHistoryResource extends JsonResource
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
        $data = $this->formData->data;
        $email_field = $form->fields()->where('type', 'email')->first();
        $fname_field = $form->fields()->where('name', 'like', '%firstname%')->orWhere('name', 'like', '%first_name%')->first();
        $lname_field = $form->fields()->where('name', 'like', '%lastname%')->orWhere('name', 'like', '%last_name%')->first();
        $fullname_field = $form->fields()->where('name', 'like', '%fullname%')->orWhere('name', 'like', '%full_name%')->first();
        $name_field = $form->fields()->where('name', 'like', '%name%')->first();

        $name = collect([
            $fname_field ? $data[$fname_field->name] ?? $this->formData->id : '',
            $lname_field ? $data[$lname_field->name] ?? '' : '',
            $fullname_field && ! $fname_field && ! $fname_field ? $data[$fullname_field->name] ?? '' : '',
            $name_field && ! $fname_field && ! $fname_field && ! $fullname_field ? $data[$name_field->name] ?? '' : '',
        ])->filter(fn ($name) => $name !== '')->implode(' ');

        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'form_data_id' => $this->form_data_id,
            'name' => $name,
            'qr' => route('form.data.qr', ['form', $this->form_data_id]),
            'email' => $data[$email_field->name],
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

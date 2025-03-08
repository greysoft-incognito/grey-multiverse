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

        $user = $request->user('sanctum');
        $fields_map = $form->config['fields_map'] ?? ['name' => 'name', 'email' => 'email', 'phone' => 'phone'];

        foreach (['name', 'email', 'phone', 'gender'] as $field) {
            if (empty($data[$fields_map[$field] ?? $field]) && $user) {
                $data[$fields_map[$field] ?? $field] = $user[$field] ?? $this->{$field};
            }
            if (empty($this->{$field}) && ! empty($user[$field])) {
                $this->{$field} = $user[$field];
            }
        }

        $name = str($this->name)->explode(' ');

        return collect($data)
            ->except(['fields'])
            ->merge([
                'id' => $this->id,
                'name' => $this->name,
                'firstname' => $name->first(),
                'lastname' => $name->count() > 1 ? $name->last() : '',
                'form_id' => $this->form_id,
                'email' => $this->whenNotNull($this->email),
                'phone' => $this->whenNotNull($this->phone),
            'qr' => $this->when($this->id, fn() => route('form.data.qr', ['form', $this->id]), null),
            'scan_date' => $form->scan_date,
            'status' => $this->status ?? 'pending',
            'rank' => $this->rank,
            'score' => $this->score,
            'draft' => $this->draft ?? ['draft_form_data' => false],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'fields' => $form->fields,
                'status_reason' => $this->status_reason,
                'reviewer' => $this->when($this->reviewer, fn() => [
                    'id' => $this->reviewer->id,
                    'name' => $this->reviewer->name
                ]),
            ]);
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

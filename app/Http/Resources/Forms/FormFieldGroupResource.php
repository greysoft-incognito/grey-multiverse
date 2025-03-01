<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use ToneflixCode\ResourceModifier\Services\Json\JsonResource;

class FormFieldGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $with = str($request->string('with'))->remove(' ')->explode(',');
        $form = !$this->id ? $request->route()->parameter('form') : null;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "icon" => $this->icon,
            "priority" => $this->priority,
            "form_id" => $this->form_id,
            "description" => $this->description,
            "field_count" => $this->id ? $this->fields()->count() : $form?->fields()->count(),
            "authenticator" => $this->authenticator,
            'requires_auth' => $this->requires_auth,
            'fields' => $this->when(
                $with->contains('fields'),
                fn() => new FormFieldCollection($this->id ? $this->fields : $form->fields ?? []),
                []
            ),
            "updated_at" => $this->updated_at,
            "created_at" => $this->created_at,
        ];
    }
}

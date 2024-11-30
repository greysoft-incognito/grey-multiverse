<?php

namespace App\Http\Resources\Forms;

use ToneflixCode\ResourceModifier\Services\Json\JsonResource;
use V1\Services\AppInfo;

class FormFieldResource extends JsonResource
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
            "id" => $this->id,
            "formId" => $this->form_id,
            "label" => $this->label,
            "name" => $this->name,
            "alias" => $this->alias,
            "value" => $this->value,
            "fieldId" => $this->field_id,
            "hint" => $this->hint,
            "customError" => $this->custom_error,
            "compare" => $this->compare,
            "options" => $this->when($this->alias === 'learning_paths' && (bool) $this->form?->learningPaths, function () {
                return collect($this->form->learningPaths)->map(function ($path) {
                    $path->label = $path->title;
                    $path->value = $path->id;

                    return $path;
                });
            }, $this->options ?: []),
            "requiredIf" => $this->required_if,
            "restricted" => $this->restricted,
            "required" => $this->required,
            "priority" => $this->priority,
            "key" => $this->key,
            "min" => $this->min,
            "max" => $this->max,
            "element" => $this->element,
            "type" => $this->type,
            "expected_value_type" => $this->ExpectedValueType,
            "createdAt" => $this->created_at,
            "updatedAt" => $this->updated_at,
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
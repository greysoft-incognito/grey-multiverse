<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use ToneflixCode\ResourceModifier\Services\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'image' => $this->files['image'],
            'description' => $this->discription,
            'industryCategory' => $this->industry_category,
            'location' => $this->location,
            'services' => $this->services,
            'conferenceObjectives' => $this->conference_objectives,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use ToneflixCode\ResourceModifier\Services\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'tableNumber' => $this->table_number,
            'timeSlot' => $this->time_slot,
            'duration' => $this->duration,
            'status' => $this->status,
            'date' => $this->date,
            'invitee' => $this->invitee->company,
            'requestor' => $this->requestor->company,
            'bookedFor' => $this->booked_for,
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
        ];
    }
}

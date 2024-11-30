<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use ToneflixCode\ResourceModifier\Services\Json\JsonResource;

class AppointmentRescheduleResource extends JsonResource
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
            'sent' => $request->user('sanctum')->is($this->invitee),
            'proposedDuration' =>  $this->proposed_duration,
            'proposedTimeSlot' =>  $this->proposed_time_slot,
            'status' =>  $this->status,
            'appointment' => new AppointmentResource($this->appointment),
            'proposedDate' =>  $this->proposed_date,
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
        ];
    }
}
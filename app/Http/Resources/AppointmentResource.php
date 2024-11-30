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
            'sent' => $request->user('sanctum')->is($this->requestor),
            'rescheduling' => $this->status === 'rescheduled' && $this->has_pending_reschedule,
            'invitee' => new CompanyResource($this->invitee->company),
            'requestor' => new CompanyResource($this->requestor->company),
            'bookedFor' => $this->booked_for,
            'endsAt' => $this->booked_for?->addMinutes($this->duration),
            'updatedAt' => $this->updated_at,
            'createdAt' => $this->created_at,
        ];
    }
}
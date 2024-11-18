<?php

namespace V1\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $time_left = $this->created_at ? $this->created_at->diffInRealHours(now()->subHours(24)->toDateTimeString()) : 0;
        $user = $this->user_type === 'guest' ? $this->guest : $this->user;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'space_id' => $this->space_id,
            'selected_space' => $this->space ? $this->space->name : '',
            'name' => $user->fullname ?? '',
            'email' => $user->email ?? '',
            'phone' => $user->phone ?? '',
            'company' => $user->company ?? [],
            'scan_date' => $this->scan_date,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'qr' => route('form.data.qr', ['reservation', $this->id]),
            'status' => $this->status,
            'paid' => $this->transaction && $this->transaction->status == 'paid',
            'time_left' => $time_left > 0 ? $time_left : 0,
            'user_type' => $this->user_type,
            'user' => $user,
            'fields' => collect([
                'selected_space' => 'Selected Space',
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'company' => 'Company',
                'start_date' => 'Start Date',
                'end_date' => 'End Date',
                'status' => 'Status',
                'paid' => 'Paid',
                'time_left' => 'Time Left (Hours)',
                'date' => 'Reservation Date',
            ])->map(function ($value, $key) {
                return [
                    'id' => $key,
                    'name' => $key,
                    'label' => $value,
                ];
            })->values(),
            'date' => $this->when($this->created_at, $this->created_at->format('Y-m-d H:i:s')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

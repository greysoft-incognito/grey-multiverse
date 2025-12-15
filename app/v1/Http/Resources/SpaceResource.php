<?php

namespace V1\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use V1\Http\Resources\User\UserCollection;
use V1\Services\AppInfo;

class SpaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $response = Gate::inspect('can-do', ['spaces.list']);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'size' => $this->size,
            'info' => $this->info,
            'price' => $this->price,
            'image_url' => $this->images['image'],
            'is_available' => $this->is_available,
            'total_occupants' => $this->total_occupants,
            'available_spots' => $this->available_spots,
            'custom_data' => $this->data,
            $this->mergeWhen($response->allowed(), function () {
                return [
                    'reservations' => new ReservationCollection($this->reservations),
                    'guests' => new UserCollection($this->guests),
                    'users' => new UserCollection($this->users),
                ];
            }),
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

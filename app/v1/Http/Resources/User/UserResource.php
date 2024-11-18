<?php

namespace V1\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use V1\Services\AppInfo;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $route = $request->route()->getName();

        return [
            'id' => $this->id,
            'avatar' => $this->avatar,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'fullname' => $this->fullname,
            'company' => $this->company,
            $this->mergeWhen($request->user() && ($request->user()->id === $this->id || $request->user()->isAdmin()) && ! in_array($route, []), [
                'email' => $this->email,
                'phone' => $this->phone,
                'last_attempt' => $this->last_attempt,
                'email_verified_at' => $this->email_verified_at,
                'phone_verified_at' => $this->phone_verified_at,
                'privileges' => $this->privileges,
            ]),
            'reg' => $this->created_at,
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

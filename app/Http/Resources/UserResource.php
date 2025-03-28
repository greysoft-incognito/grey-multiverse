<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use ToneflixCode\ResourceModifier\Services\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User|UserResource $this */
        $iam_admin = $request?->user()?->hasAnyRole(config('permission-defs.admin_roles', [])) ||
            $this->hasAnyPermission(['manage-users', 'manage-admins']) ||
            false;

        $previleged = auth()?->id() === $this->id || $iam_admin;

        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'fullName' => $this->fullname,
            'imageUrl' => $this->files['image'],
            'email' => $this->email,
            'type' => $this->type ?: 'User',
            'phone' => $this->phone,
            'address' => $this->address,
            'country' => $this->country,
            'company' => new CompanyResource($this->company),
            'organization' => $this->company->name ?? '',
            'state' => $this->state,
            'city' => $this->city,
            'reg_status' => $this->reg_status ?? 'pending',
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'last_attempt' => $this->last_attempt,
            $this->mergeWhen($previleged, fn () => [
                'roles' => $this->getRoleNames()->values(),
                'permissions' => $this->getAllPermissions()->pluck('name')->unique()->values(),
            ]),
            'user_data' => $this->data,
            'access_data' => $this->access_data,
        ];
    }
}

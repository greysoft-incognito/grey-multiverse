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
        $iam_admin = $request?->user()?->hasAnyRole(config('permission-defs.admin_roles', [])) ||
            $this->hasAnyPermission(['manage-users', 'manage-admins']) ||
            false;

        $previleged = auth()?->id() === $this->id || $iam_admin;

        $permissions = [];
        if ($previleged) {
            $permQuery = $this->getPermissionsViaRoles()->pluck('name')->unique();
            $permissions = $permQuery->count() ? $permQuery->values() : $this->getPermissionNames();
        }

        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'image' => $this->files['image'],
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'password' => $this->password,
            'last_attempt' => $this->last_attempt,
            $this->mergeWhen($previleged, fn () => [
                'roles' => $this->getRoleNames(),
                'permissions' => $permissions,
            ]),
            'user_data' => $this->data,
            'access_data' => $this->access_data,
        ];
    }
}

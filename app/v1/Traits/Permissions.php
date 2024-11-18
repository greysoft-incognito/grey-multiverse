<?php

namespace V1\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait Permissions
{
    protected $user;

    protected $allowed = [
        'admin' => [
            'users.list',
            'users.create',
            'users.user',
            'users.update',
            'users.delete',

            'form.list',
            'form.show',
            'form.create',
            'form.update',
            'form.delete',

            'formfield.list',
            'formfield.show',
            'formfield.create',
            'formfield.update',
            'formfield.delete',

            'formdata.stats',
            'formdata.list',
            'formdata.show',

            'dashboard',
            'content.create',
            'content.update',
            'content.delete',
            'front_content',
            'subscriptions',
            'transactions',
            'configuration',

            'spaces',
            'spaces.list',
            'spaces.show',
            'spaces.create',
            'spaces.update',
            'spaces.delete',

            'reservation.list',
            'reservation.show',
            'reservation.create',
            'reservation.update',
            'reservation.delete',
        ],
        'manager' => [
            'users.list',
            'users.user',

            'form.list',
            'form.show',
            'form.create',
            'form.update',
            'form.delete',

            'formfield.list',
            'formfield.show',
            'formfield.create',
            'formfield.update',
            'formfield.delete',

            'formdata.stats',
            'formdata.list',
            'formdata.show',

            'dashboard',
            'subscriptions',
            'transactions',

            'spaces',
            'spaces.list',
            'spaces.show',
            'spaces.create',
            'spaces.update',
            'spaces.delete',

            'reservation.list',
            'reservation.show',
            'reservation.create',
            'reservation.update',
            'reservation.delete',
        ],
        'reviewer' => [
            'form.list',
            'form.show',

            'formdata.stats',
            'formfield.list',
            'formfield.show',

            'formdata.list',
            'formdata.show',

            'dashboard',

            'spaces.list',
            'spaces.show',

            'reservation.list',
            'reservation.show',
        ],
    ];

    /**
     * Set the user
     */
    public function setPermissionsUser(User $user): Permissions
    {
        $this->privileges = $user->privileges;

        return $this;
    }

    /**
     * Check if the user has the requested permission
     *
     * @param  string  $permission
     */
    public function checkPermissions(string|Model $permission): string|bool
    {
        if ($this->listPrivileges()->contains($permission)) {
            foreach (($this->privileges ?? []) as $user_permission) {
                if (collect($this->allowed[$user_permission])->contains($permission) ||
                collect($this->allowed[$user_permission])->contains(str($permission)->explode('.')->first())) {
                    return true;
                }
                if (in_array($permission, $this->allowed[$user_permission], true)) {
                    return true;
                }
            }
        } elseif (($permission instanceof Model &&
                  $permission->user_id && $permission->user_id === $this->auth_user->id) ||
                  (is_numeric($permission) && $permission === $this->auth_user->id)
        ) {
            return true;
        }

        return 'You do not have permission to view or perform this action.';
    }

    /**
     * Get a list of all available privileges
     *
     * @return \Illuminate\Support\Collection<TKey, TValue>
     */
    public function listPrivileges($key = null)
    {
        if ($key && collect($this->allowed)->has($key)) {
            return collect($this->allowed[$key])->flatten();
        }

        return collect($this->allowed)->flatten();
    }

    /**
     * Get a list of all available permissions
     *
     * @return \Illuminate\Support\Collection<TKey, TValue>
     */
    public function getPermissions()
    {
        $permissions = [];
        foreach (($this->privileges ?? []) as $user_permission) {
            $permissions[] = $this->allowed[$user_permission];
        }

        return collect($permissions)->flatten()->toArray();
    }

    /**
     * Check if the user has the admin priviledge
     */
    public function isAdmin(): bool
    {
        foreach (($this->privileges ?? []) as $user_permission) {
            if ($user_permission === 'admin') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has the given priviledge
     */
    public function hasPermission($permission = 'admin'): bool
    {
        foreach (($this->privileges ?? []) as $user_permission) {
            if ($user_permission === $permission) {
                return true;
            }
        }

        return false;
    }
}

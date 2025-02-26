<?php

namespace V1\Providers;

use App\Models\User as V1User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use V1\Traits\Permissions;

class AuthServiceProvider extends ServiceProvider
{
    use Permissions;

    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        if (str($request->url())->contains('api/v1')) {
            $this->registerPolicies();

            Gate::define('usable', function (V1User $user, $permission) {
                return ($check = $this->setPermissionsUser($user)->checkPermissions($permission)) === true
                    ? Response::allow()
                    : Response::deny($check);
            });

            Gate::define('can-do', function (V1User $user, $permission, $item = null) {
                return ($check = $this->setPermissionsUser($user)->checkPermissions($permission)) === true
                    ? Response::allow()
                    : Response::deny($check);
            });
        } else {
            Gate::define('usable', function (V1User $user, $permission) {
                $permissions = is_array($permission) ? $permission : [$permission];

                $pname = str(collect($permissions)->join(', '))->replace('.', ' ')->headline()->lower();

                return $user->hasAllPermissions($permission)
                    ? Response::allow()
                    : Response::deny(__('You do not have the ":0" permission.', [$pname]));
            });

            Gate::define('can-do', function (V1User $user, $permission, $item = null) {
                $permissions = is_array($permission) ? $permission : [$permission];

                $pname = str(collect($permissions)->join(', '))->replace('.', ' ')->headline()->lower();

                return $user->hasAllPermissions($permission)
                    ? Response::allow()
                    : Response::deny(__('You do not have the ":0" permission.', [$pname]));
            });

            Gate::after(function ($user) {
                return $user->hasRole(config('permission-defs.super-admin-role'));
            });
        }
    }
}

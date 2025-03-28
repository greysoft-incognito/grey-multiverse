<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncFormRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        form:sync-roles
            {form? : The ID(s) of the form(s) to create the roles and permissions to}
            {users?* : The ID(s) of the user(s) to assign the roles and permissions to}
            {--x|remove : Remove the roles and permissions from the user(s)}
            {--r|roles=* : The roles to assign to the user(s)}
            {--p|permissions=* : The permissions to assign to the user(s)}
            {--f|force : Force the operation to run when in production (Not Implemented)}
            {--s|supes : Force grant the super admin role role}
            {--t|reset : Resets the permissions and roles, when makin (Use with caution)}
            {--m|make : Create the roles and permissions (Runs by default if user(s) are not specified)}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync form roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('show')) {
            $this->show();
        } elseif ($this->option('remove')) {
            $this->remove();
        } elseif ($this->option('make') || empty($this->argument('users'))) {
            if ($this->option('reset')) {
                $this->truncate();
            }
            $this->make();
        } else {
            $this->sync();
        }
    }

    /**
     * Remove the roles and permissions from the specified user(s).
     */
    public function remove()
    {
        $users = $this->argument('users');
        $roles = $this->option('roles');
        $supes = $this->option('supes');
        $permissions = $this->option('permissions');

        /** @var \Illuminate\Database\Eloquent\Collection<TKey,\App\Models\User> */
        $users = app(config('permission-defs.user-model', []))->findMany($users);

        $conf = str_ireplace(
            ["\n", "\t", '  '],
            ["\n ", '', ''],
            'You have not specified any roles or permissions.
            Do you want to remove all roles from the user(s) ' .
                (! $supes
                ? '(This excludes the "' . config('permission-defs.super-admin-role', 'super-admin') . ' role)?'
                    : ''
                )
        );

        if (empty($permissions) && empty($roles)) {
            // Check if the command is running in console then prompt the user to confirm.
            if (app()->runningInConsole() && $this->confirm($conf)) {
                $roles = Role::whereNotIn(
                    'name',
                    ! $supes ? [config('permission-defs.super-admin-role', 'super-admin')] : []
                )->pluck('name');
            } else {
                $this->error('No roles or permissions were specified. Exiting...');

                return;
            }
        } elseif (! empty($roles)) {
            $roles = Role::whereIn('name', $roles)->orWhereIn('id', $roles)->pluck('name');
        }

        if (! empty($permissions)) {
            $permissions = Permission::whereIn('name', $permissions)->orWhereIn('id', $permissions)->pluck('name');
        } else {
            $permissions = collect();
        }

        $users->each(function ($user) use ($roles, $permissions) {
            $roles->each(fn($role) => $user->removeRole($role));
            $permissions->each(fn($permission) => $user->revokePermissionTo($permission));
        });

        $this->info('Roles');
        $this->table(
            ['ID', 'Name', 'Gaurds', 'Roles'],
            $users->map(fn($user) => [
                $user->id,
                $user->firstname,
                $user->roles->pluck('guard_name')->implode(', '),
                $user->roles->pluck('name')->implode(', '),
            ])
        );

        $this->newLine();
        $this->info('Permissions');
        $this->table(
            ['ID', 'Name', 'Gaurds', 'Permissions'],
            $users->map(fn($user) => [
                $user->id,
                $user->firstname,
                $user->roles->pluck('guard_name')->implode(', '),
                $user->roles->pluck('permissions')->implode(', '),
            ])
        );
    }

    /**
     * Sync the roles and permissions to the specified user(s).
     */
    public function sync()
    {
        $users = $this->argument('users');
        $roles = $this->option('roles');
        $supes = $this->option('supes');
        $permissions = $this->option('permissions');

        /** @var \Illuminate\Database\Eloquent\Collection<TKey,\App\Models\User> */
        $users = app(config('permission-defs.user-model', []))->findMany($users);

        $conf = str_ireplace(
            ["\n", "\t", '  '],
            ["\n ", '', ''],
            'You have not specified any roles or permissions.
            Do you want to assign all roles to the user(s) ' .
                (! $supes
                ? '(This excludes the "' . config('permission-defs.super-admin-role', 'super-admin') . ' role)?'
                    : ''
                )
        );

        if (empty($permissions) && empty($roles)) {
            // Check if the command is running in console then prompt the user to confirm.
            if (app()->runningInConsole() && $this->confirm($conf)) {
                $roles = Role::whereNotIn(
                    'name',
                    ! $supes ? [config('permission-defs.super-admin-role', 'super-admin')] : []
                )->pluck('name');
            } else {
                $this->error('No roles or permissions were specified. Exiting...');

                return;
            }
        } elseif (! empty($roles)) {
            $roles = Role::whereIn('name', $roles)->orWhereIn('id', $roles)->pluck('name');
        }

        if (! empty($permissions)) {
            $permissions = Permission::whereIn('name', $permissions)->orWhereIn('id', $permissions)->pluck('name');
        }

        $users->each(function ($user) use ($roles, $permissions) {
            $user->syncRoles($roles);
            $user->syncPermissions($permissions);
        });

        $this->info('Roles');
        $this->table(
            ['ID', 'Name', 'Gaurds', 'Roles'],
            $users->map(fn($user) => [
                $user->id,
                $user->firstname,
                $user->roles->pluck('guard_name')->implode(', '),
                $user->roles->pluck('name')->implode(', '),
            ])
        );

        $this->newLine();
        $this->info('Permissions');
        $this->table(
            ['ID', 'Name', 'Gaurds', 'Permissions'],
            $users->map(fn($user) => [
                $user->id,
                $user->firstname,
                $user->roles->pluck('guard_name')->implode(', '),
                $user->roles->pluck('permissions')->implode(', '),
            ])
        );
    }

    public function truncate(): void
    {
        if (app()->runningInConsole() && $this->confirm('Are you sure you want to reset all permissions and roles?')) {
            Schema::withoutForeignKeyConstraints(function () {
                Role::query()->delete();
                Permission::query()->delete();

                Permission::truncate();
                Role::truncate();
            });
        }
    }

    /**
     * Run the database seeds.
     */
    public function make()
    {
        $rolesArray = collect(config('permission-defs.roles', []))->sort();
        $permissionsArray = collect(config('permission-defs.permissions', []))->sort();

        $formRolesArray = collect(config('permission-defs.form_roles', []))->sort();
        $formPermissionsArray = collect(config('permission-defs.form_permissions', []))->sort();

        $rolesArray->each(fn($role) => Role::findOrCreate($role));
        $permissionsArray->each(fn($role) => Permission::findOrCreate($role));

        $formRolesArray->each(fn($role) => Role::findOrCreate($role));
        $formPermissionsArray->each(fn($role) => Permission::findOrCreate($role));

        $roles = Role::whereNot('name', 'like', 'form.%')->withCount('permissions')->get();
        $formRoles = Role::where('name', 'like', 'form.%')->withCount('permissions')->get();
        $permissions = Permission::get();

        // Sync Main Roles
        $roles->each(function ($role) use ($permissionsArray) {
            $exclude = config("permission-defs.exclusions.{$role->name}", []);
            $role->syncPermissions($permissionsArray->filter(fn($perm) => ! in_array($perm, $exclude)));
        });

        // Sync Form Roles
        $formRoles->each(function ($role) use ($formPermissionsArray) {
            $exclude = config("permission-defs.form_exclusions.{$role->name}", []);
            $role->syncPermissions($formPermissionsArray->filter(fn($perm) => ! in_array($perm, $exclude)));
        });

        $this->info('Roles');
        $this->table(
            ['ID', 'Name', 'Gaurd', 'Permissions'],
            $roles->map(fn($role) => $role->only('id', 'name', 'guard_name', 'permissions_count'))
        );

        $this->newLine();

        $this->info('Form Roles');
        $this->table(
            ['ID', 'Name', 'Gaurd', 'Permissions'],
            $formRoles->map(fn($role) => $role->only('id', 'name', 'guard_name', 'permissions_count'))
        );
        $this->info('Roles Synced');

        $this->newLine();
        $this->info('Permissions');
        $this->table(
            ['ID', 'Name', 'Gaurd'],
            $permissions->map(fn($perm) => $perm->only('id', 'name', 'guard_name'))
        );
        $this->info('Permissions Synced');
    }
}

<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\PermissionRegistrar;

trait HasExtendedPermissions
{
    use HasExtendedRolesAndPermissionsContext;

    public function contextPermissions(): BelongsToMany
    {
        $cntx = $this->getCurrentContext();

        $relation = $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            app(PermissionRegistrar::class)->pivotPermission
        );

        if ($cntx) {
            $teamId = $this->getTeamIdentifier($cntx);

            return $relation->withPivotValue('team_id', $teamId);
        }

        return $relation;
    }

    public function syncContextPermissions(array|string ...$ids)
    {
        $permissions = collect($ids)->flatten();

        return $this->contextPermissions()->sync($permissions);
    }

    /**
     * Check if the model has any of the given permissions in the current context.
     *
     * @param  string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection  $permissions
     */
    public function checkAnyPermissionInContext(...$permissions): bool
    {
        $cntx = $this->getCurrentContext();
        $cntxId = $this->getTeamIdentifier($cntx);

        if (! $cntx) {
            // No context: fall back to global permissions using Spatie's method
            return parent::hasAnyPermission(...$permissions);
        }

        // Convert permissions to a collection of names or models
        $permissions = collect($this->convertToPermissionModels(...$permissions));

        // Check direct permissions in the context
        if ($this->permissions()->whereIn('name', $permissions->pluck('name'))->exists()) {
            return true;
        }

        // Check via roles in the context
        $results = $this->roles()
            ->whereHas('permissions', fn ($q) => $q->whereIn('name', $permissions->pluck('name')))
            ->wherePivot('team_id', $cntxId)
            ->exists();

        $this->resetContext();

        return $results;
    }
}

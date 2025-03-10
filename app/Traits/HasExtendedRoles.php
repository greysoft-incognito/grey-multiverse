<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\PermissionRegistrar;

trait HasExtendedRoles
{
    use HasExtendedRolesAndPermissionsContext;

    public function contextRoles(): BelongsToMany
    {
        $cntx = $this->getCurrentContext();

        $relation = $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            app(PermissionRegistrar::class)->pivotRole
        );

        if ($cntx) {
            $teamId = $this->getTeamIdentifier($cntx);
            return $relation->withPivotValue('team_id', $teamId);
        }

        return $relation;
    }

    public function syncContextRoles(array|string ...$ids)
    {
        $cntx = $this->getCurrentContext();

        $permissions = collect($ids)->flatten()->mapWithKeys(fn($id) => [
            $id => $this->getTeamIdentifier($cntx)
        ]);

        return $this->contextRoles()->sync($permissions);
    }
}

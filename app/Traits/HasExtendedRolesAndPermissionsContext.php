<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasExtendedRolesAndPermissionsContext
{
    public ?Model $currentAccessContext = null;

    public function getCurrentContext(): ?Model
    {
        return property_exists($this, 'currentAccessContext') ? $this->currentAccessContext : null;
    }

    public function forContext(Model $context): self
    {
        $this->currentAccessContext = $context;
        setPermissionsTeamId($this->getTeamIdentifier($context));
        return $this;
    }

    public function resetContext(): self
    {
        $this->currentAccessContext = null;
        setPermissionsTeamId(null);
        return $this;
    }

    protected function getTeamIdentifier($context): string
    {
        $type = strtolower(class_basename($context));
        return "{$type}:{$context->id}";
    }
}

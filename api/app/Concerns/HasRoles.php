<?php

namespace App\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string ...$names): bool
    {
        return $this->loadMissing('roles')->roles->pluck('name')->intersect($names)->isNotEmpty();
    }

    /** Flat, de-duplicated list of permission names across all of this user's roles. */
    public function permissionNames(): Collection
    {
        return $this->loadMissing('roles.permissions')
            ->roles
            ->flatMap(fn ($role) => $role->permissions)
            ->pluck('name')
            ->unique()
            ->values();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissionNames()->contains($permission);
    }
}
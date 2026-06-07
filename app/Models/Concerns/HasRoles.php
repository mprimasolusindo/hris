<?php

namespace App\Models\Concerns;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    /** @var Collection<int, string>|null */
    protected ?Collection $cachedPermissionKeys = null;

    /** @var Collection<int, string>|null */
    protected ?Collection $cachedRoleSlugs = null;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'rel_user_roles', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function hasRole(string $slug): bool
    {
        return $this->roleSlugs()->contains($slug);
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roleSlugs()->intersect($slugs)->isNotEmpty();
    }

    public function hasPermission(string $key): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->permissionKeys()->contains($key);
    }

    /**
     * @return Collection<int, string>
     */
    public function roleSlugs(): Collection
    {
        if ($this->cachedRoleSlugs !== null) {
            return $this->cachedRoleSlugs;
        }

        $this->loadMissing('roles');

        return $this->cachedRoleSlugs = $this->roles->pluck('slug');
    }

    /**
     * @return Collection<int, string>
     */
    public function permissionKeys(): Collection
    {
        if ($this->cachedPermissionKeys !== null) {
            return $this->cachedPermissionKeys;
        }

        if ($this->hasRole('super-admin')) {
            return $this->cachedPermissionKeys = Permission::query()->pluck('key');
        }

        $this->loadMissing('roles.permissions');

        return $this->cachedPermissionKeys = $this->roles
            ->flatMap(fn (Role $role) => $role->permissions->pluck('key'))
            ->unique()
            ->values();
    }

    public function clearPermissionCache(): void
    {
        $this->cachedPermissionKeys = null;
        $this->cachedRoleSlugs = null;
    }
}

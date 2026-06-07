<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SuperAdminGuard
{
    public function superAdminRole(): ?Role
    {
        return Role::query()->where('slug', 'super-admin')->first();
    }

    public function superAdminCount(): int
    {
        $role = $this->superAdminRole();
        if (! $role) {
            return 0;
        }

        return $role->users()->count();
    }

    public function userHasSuperAdmin(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function ensureNotLastSuperAdmin(User $user): void
    {
        if (! $this->userHasSuperAdmin($user)) {
            return;
        }

        if ($this->superAdminCount() <= 1) {
            throw ValidationException::withMessages([
                'roles' => 'Cannot remove or delete the last super admin.',
            ]);
        }
    }

    /**
     * @param  array<int, int>  $roleIds
     */
    public function ensureSuperAdminRemains(array $roleIds, ?User $targetUser = null): void
    {
        $superAdmin = $this->superAdminRole();
        if (! $superAdmin) {
            return;
        }

        $willHaveSuperAdmin = in_array($superAdmin->id, $roleIds, true);
        if ($willHaveSuperAdmin) {
            return;
        }

        if ($targetUser && $this->userHasSuperAdmin($targetUser)) {
            $this->ensureNotLastSuperAdmin($targetUser);
        }
    }
}

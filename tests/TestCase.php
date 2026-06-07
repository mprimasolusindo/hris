<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('sys_permissions') && Role::query()->count() === 0) {
            $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        }
    }

    public function actingAs(Authenticatable $user, $guard = null): static
    {
        if ($user instanceof User && Schema::hasTable('sys_roles') && $user->roles()->count() === 0) {
            $superAdmin = Role::query()->where('slug', 'super-admin')->first();
            if ($superAdmin) {
                $user->roles()->syncWithoutDetaching([$superAdmin->id]);
                $user->clearPermissionCache();
            }
        }

        return parent::actingAs($user, $guard);
    }

    protected function createUserWithRole(string $roleSlug, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->sync([$role->id]);
        $user->clearPermissionCache();

        return $user;
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    }

    public function test_payroll_admin_cannot_create_employees(): void
    {
        $user = $this->createUserWithRole('payroll-admin');

        $this->actingAs($user);

        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Payroll Only',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('employees.store'), [
            'company_id' => $companyId,
            'employee_code' => 'EMP-RBAC-001',
            'full_name' => 'Blocked User',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    public function test_super_admin_can_create_employees(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $this->actingAs($user);

        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Super Admin',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('employees.store'), [
            'company_id' => $companyId,
            'employee_code' => 'EMP-RBAC-002',
            'full_name' => 'Allowed User',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('emp_employees', [
            'employee_code' => 'EMP-RBAC-002',
        ]);
    }

    public function test_user_can_be_created_with_multiple_roles(): void
    {
        $admin = $this->createUserWithRole('super-admin');
        $this->actingAs($admin);

        $hrAdmin = Role::query()->where('slug', 'hr-admin')->firstOrFail();
        $manager = Role::query()->where('slug', 'manager')->firstOrFail();

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Multi Role User',
            'email' => 'multi@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$hrAdmin->id, $manager->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'multi@example.test')->firstOrFail();
        $this->assertCount(2, $user->roles);
        $this->assertTrue($user->hasRole('hr-admin'));
        $this->assertTrue($user->hasRole('manager'));
    }

    public function test_cannot_remove_own_super_admin_role(): void
    {
        $admin = $this->createUserWithRole('super-admin');
        $hrAdmin = Role::query()->where('slug', 'hr-admin')->firstOrFail();

        $this->actingAs($admin);

        $response = $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'roles' => [$hrAdmin->id],
        ]);

        $response->assertForbidden();
        $this->assertTrue($admin->fresh()->hasRole('super-admin'));
    }

    public function test_cannot_delete_own_account_via_admin(): void
    {
        $admin = $this->createUserWithRole('super-admin');
        $this->actingAs($admin);

        $response = $this->delete(route('admin.users.destroy', $admin));

        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_users_index_requires_users_view_permission(): void
    {
        $employee = $this->createUserWithRole('employee');
        $this->actingAs($employee);

        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_admin_can_access_users_index(): void
    {
        $admin = $this->createUserWithRole('super-admin');
        $this->actingAs($admin);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Users/Index'));
    }
}

<?php

namespace Tests\Feature\Employee;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSubResourceTest extends TestCase
{
    use RefreshDatabase;

    private function seedCompanyAndEmployee(): Employee
    {
        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Sub Resource',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Employee::query()->create([
            'company_id' => $companyId,
            'employee_code' => 'EMP-SUB-1',
            'full_name' => 'Sub Resource Test',
            'status' => 'active',
        ]);
    }

    public function test_identity_can_be_saved(): void
    {
        $user = User::factory()->create();
        $employee = $this->seedCompanyAndEmployee();

        $response = $this->actingAs($user)->post(route('employees.identity.store', $employee), [
            'nik' => '3201010101010001',
            'npwp' => '12.345.678.9-012.345',
            'bpjs_health' => '1234567890',
            'bpjs_employment' => '0987654321',
            'address' => 'Jl. Test',
            'city' => 'Jakarta',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('emp_identities', [
            'employee_id' => $employee->id,
            'nik' => '3201010101010001',
        ]);
    }

    public function test_family_member_can_be_updated_with_dependent_flag(): void
    {
        $user = User::factory()->create();
        $employee = $this->seedCompanyAndEmployee();

        $familyId = \DB::table('emp_family_members')->insertGetId([
            'employee_id' => $employee->id,
            'name' => 'Old Name',
            'relationship' => 'child',
            'is_dependent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->put(
            route('employees.family-members.update', [$employee, $familyId]),
            [
                'name' => 'New Name',
                'relationship' => 'spouse',
                'birth_date' => '1990-01-01',
                'is_dependent' => false,
            ],
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('emp_family_members', [
            'id' => $familyId,
            'name' => 'New Name',
            'relationship' => 'spouse',
            'is_dependent' => false,
        ]);
    }

    public function test_family_member_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $employee = $this->seedCompanyAndEmployee();

        $familyId = \DB::table('emp_family_members')->insertGetId([
            'employee_id' => $employee->id,
            'name' => 'To Delete',
            'relationship' => 'sibling',
            'is_dependent' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete(
            route('employees.family-members.destroy', [$employee, $familyId]),
        );

        $response->assertRedirect();
        $this->assertSoftDeleted('emp_family_members', ['id' => $familyId]);
    }

    public function test_identity_rejects_non_numeric_nik(): void
    {
        $user = User::factory()->create();
        $employee = $this->seedCompanyAndEmployee();

        $response = $this->actingAs($user)->post(route('employees.identity.store', $employee), [
            'nik' => '32010101ABCD0001',
        ]);

        $response->assertSessionHasErrors('nik');
    }

    public function test_allowance_can_be_created(): void
    {
        $user = User::factory()->create();
        $employee = $this->seedCompanyAndEmployee();

        $componentId = \DB::table('cfg_salary_components')->insertGetId([
            'name' => 'Transport',
            'code' => 'transport',
            'type' => 'earning',
            'calculation_method' => 'fixed',
            'default_value' => 500000,
            'is_taxable' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('employees.allowances.store', $employee), [
            'component_id' => $componentId,
            'name' => 'Transport',
            'amount' => 500000,
            'taxable' => true,
            'status' => 'active',
            'recurring' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('emp_allowances', [
            'employee_id' => $employee->id,
            'name' => 'Transport',
        ]);
    }
}

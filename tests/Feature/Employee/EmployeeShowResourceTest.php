<?php

namespace Tests\Feature\Employee;

use App\Http\Resources\Employee\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeShowResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_resource_serializes_nested_relations_as_arrays(): void
    {
        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Resource Test',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employee = Employee::query()->create([
            'company_id' => $companyId,
            'employee_code' => 'EMP-RES-1',
            'full_name' => 'Resource Test',
            'status' => 'active',
        ]);

        \DB::table('emp_family_members')->insert([
            'employee_id' => $employee->id,
            'name' => 'Spouse',
            'relationship' => 'spouse',
            'is_dependent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employee->load(['familyMembers', 'allowances', 'deductions', 'documents', 'emergencyContacts', 'bankAccounts']);

        $payload = (new EmployeeResource($employee))->resolve();

        $this->assertIsArray($payload['family_members']);
        $this->assertCount(1, $payload['family_members']);
        $this->assertIsArray($payload['allowances']);
        $this->assertIsArray($payload['deductions']);
    }

    public function test_employee_show_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Show',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employee = Employee::query()->create([
            'company_id' => $companyId,
            'employee_code' => 'EMP-SHOW-1',
            'full_name' => 'Show Test',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('employees.show', $employee))
            ->assertOk();
    }
}

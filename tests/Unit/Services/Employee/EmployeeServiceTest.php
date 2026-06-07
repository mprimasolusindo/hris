<?php

namespace Tests\Unit\Services\Employee;

use App\Models\Employee;
use App\Services\Employee\EmployeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_employee(): void
    {
        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Test',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(EmployeeService::class);
        $employee = $service->create([
            'company_id' => $companyId,
            'employee_code' => 'EMP-TDD-1',
            'full_name' => 'Test Employee',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertDatabaseHas('emp_employees', ['employee_code' => 'EMP-TDD-1']);
    }

    public function test_update_changes_fields(): void
    {
        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Test 2',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employee = Employee::query()->create([
            'company_id' => $companyId,
            'employee_code' => 'EMP-TDD-2',
            'full_name' => 'Before',
            'status' => 'active',
        ]);

        $service = app(EmployeeService::class);
        $updated = $service->update($employee, ['full_name' => 'After']);

        $this->assertSame('After', $updated->full_name);
    }
}

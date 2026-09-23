<?php

namespace Tests\Feature;

use App\Models\BpjsConfig;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\TaxRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollStoreContractSalaryTest extends TestCase
{
    use RefreshDatabase;

    private function seedPayrollPrerequisites(int $employeeId): void
    {
        BpjsConfig::query()->create([
            'type' => 'kesehatan',
            'employee_percentage' => 0.0100,
            'company_percentage' => 0.0400,
        ]);

        TaxRule::query()->create([
            'name' => 'ter_monthly_A_contract_salary',
            'rule_type' => 'ter_monthly',
            'ptkp_category' => 'A',
            'gross_min' => 0,
            'gross_max' => null,
            'value' => 0.0500,
        ]);

        \DB::table('emp_tax_profiles')->insert([
            'employee_id' => $employeeId,
            'has_npwp' => true,
            'tax_status' => 'TK/0',
            'tax_method' => 'ter_monthly',
            'dependents_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_payroll_store_resolves_base_salary_from_active_contract(): void
    {
        $this->actingAs(User::factory()->create());

        $employee = Employee::factory()->create();
        $this->seedPayrollPrerequisites($employee->id);

        $salaryBase = 8500000;
        EmploymentContract::query()->create([
            'employee_id' => $employee->id,
            'contract_type' => 'pkwtt',
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'salary_base' => $salaryBase,
        ]);

        $month = (int) now()->month;
        $year = (int) now()->year;

        $response = $this->post(route('payroll.store'), [
            'employee_id' => $employee->id,
            'period_month' => $month,
            'period_year' => $year,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pay_payroll_items', [
            'component_name' => 'Base Salary',
            'amount' => $salaryBase,
        ]);
    }

    public function test_payroll_store_fails_when_no_active_contract_for_period(): void
    {
        $this->actingAs(User::factory()->create());

        $employee = Employee::factory()->create();

        $month = (int) now()->month;
        $year = (int) now()->year;

        $response = $this->post(route('payroll.store'), [
            'employee_id' => $employee->id,
            'period_month' => $month,
            'period_year' => $year,
        ]);

        $response->assertSessionHasErrors('employee_id');
        $this->assertDatabaseCount('pay_payrolls', 0);
    }
}

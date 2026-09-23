<?php

namespace Tests\Feature;

use App\Models\BpjsConfig;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\TaxRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollStoreGenerateAllTest extends TestCase
{
    use RefreshDatabase;

    private function seedPayrollPrerequisites(int $employeeId, bool $withGlobalConfig = true): void
    {
        if ($withGlobalConfig) {
            BpjsConfig::query()->create([
                'type' => 'kesehatan',
                'employee_percentage' => 0.0100,
                'company_percentage' => 0.0400,
            ]);

            TaxRule::query()->create([
                'name' => 'ter_monthly_A_generate_all',
                'rule_type' => 'ter_monthly',
                'ptkp_category' => 'A',
                'gross_min' => 0,
                'gross_max' => null,
                'value' => 0.0500,
            ]);
        }

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

    public function test_store_scope_all_generates_for_active_employees_with_contracts(): void
    {
        $this->actingAs(User::factory()->create());

        $company = Company::factory()->create();

        $withContract = Employee::factory()->create(['status' => 'active', 'company_id' => $company->id]);
        $withoutContract = Employee::factory()->create(['status' => 'active', 'company_id' => $company->id]);
        $resigned = Employee::factory()->create(['status' => 'resigned', 'company_id' => $company->id]);

        $this->seedPayrollPrerequisites($withContract->id, true);
        $this->seedPayrollPrerequisites($withoutContract->id, false);
        $this->seedPayrollPrerequisites($resigned->id, false);

        EmploymentContract::query()->create([
            'employee_id' => $withContract->id,
            'contract_type' => 'pkwtt',
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'salary_base' => 5000000,
        ]);

        EmploymentContract::query()->create([
            'employee_id' => $resigned->id,
            'contract_type' => 'pkwtt',
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'salary_base' => 4000000,
        ]);

        $month = (int) now()->month;
        $year = (int) now()->year;

        $response = $this->post(route('payroll.store'), [
            'scope' => 'all',
            'period_month' => $month,
            'period_year' => $year,
        ]);

        $response->assertRedirect(route('payroll.index'));
        $response->assertSessionHas(
            'success',
            'Generated 1 payroll(s). Skipped 1 (no active contract).'
        );
        $this->assertDatabaseCount('pay_payrolls', 1);
        $this->assertDatabaseHas('pay_payrolls', [
            'employee_id' => $withContract->id,
            'period_month' => $month,
            'period_year' => $year,
        ]);
        $this->assertDatabaseMissing('pay_payrolls', [
            'employee_id' => $resigned->id,
        ]);
        $this->assertDatabaseMissing('pay_payrolls', [
            'employee_id' => $withoutContract->id,
        ]);
    }

    public function test_store_single_employee_still_requires_employee_id(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('payroll.store'), [
            'period_month' => (int) now()->month,
            'period_year' => (int) now()->year,
        ]);

        $response->assertSessionHasErrors('employee_id');
    }
}

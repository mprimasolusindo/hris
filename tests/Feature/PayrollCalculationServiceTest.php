<?php

namespace Tests\Feature;

use App\Models\BpjsConfig;
use App\Models\Employee;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeLoan;
use App\Models\EmployeeTaxProfile;
use App\Models\TaxRule;
use App\Services\Payroll\PayrollCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedStatutoryConfig(): void
    {
        BpjsConfig::query()->create(['type' => 'kesehatan', 'employee_percentage' => '0.0100', 'company_percentage' => '0.0400']);
        BpjsConfig::query()->create(['type' => 'jht', 'employee_percentage' => '0.0200', 'company_percentage' => '0.0370']);
        BpjsConfig::query()->create(['type' => 'jp', 'employee_percentage' => '0.0100', 'company_percentage' => '0.0200']);
        // Employer-borne — should NOT produce an employee deduction line.
        BpjsConfig::query()->create(['type' => 'jkk', 'employee_percentage' => '0.0000', 'company_percentage' => '0.0024']);

        TaxRule::query()->create([
            'name' => 'ter_monthly_A_test',
            'rule_type' => 'ter_monthly',
            'ptkp_category' => 'A',
            'gross_min' => 0,
            'gross_max' => null,
            'value' => '0.0500',
        ]);
    }

    public function test_it_builds_compliance_grade_payroll_lines(): void
    {
        $this->seedStatutoryConfig();

        $employee = Employee::factory()->create(['join_date' => now()->subYears(3)]);
        EmployeeTaxProfile::query()->create([
            'employee_id' => $employee->id,
            'has_npwp' => true,
            'tax_status' => 'TK/0',
            'tax_method' => 'ter_monthly',
            'dependents_count' => 0,
        ]);
        EmployeeAllowance::query()->create([
            'employee_id' => $employee->id,
            'name' => 'Tunjangan Transport',
            'amount' => 500000,
            'taxable' => true,
            'status' => 'active',
            'recurring' => true,
        ]);
        EmployeeLoan::query()->create([
            'employee_id' => $employee->id,
            'amount' => 6000000,
            'remaining_amount' => 3000000,
            'monthly_deduction' => 500000,
        ]);

        $base = 5000000.0;
        $payroll = app(PayrollCalculationService::class)->generate($employee, 3, 2026, $base);

        // Gross = base + transport allowance (no attendance rows seeded, March → no THR).
        $this->assertEqualsWithDelta(5500000.0, (float) $payroll->gross_salary, 0.01);

        $items = $payroll->items->keyBy('component_name');

        $this->assertEqualsWithDelta(50000.0, (float) $items['BPJS KESEHATAN (Employee)']->amount, 0.01);
        $this->assertEqualsWithDelta(100000.0, (float) $items['BPJS JHT (Employee)']->amount, 0.01);
        $this->assertEqualsWithDelta(50000.0, (float) $items['BPJS JP (Employee)']->amount, 0.01);
        // JKK is employer-borne, so no employee line.
        $this->assertArrayNotHasKey('BPJS JKK (Employee)', $items->toArray());

        // PPh21 TER A: 5% of gross 5,500,000 = 275,000.
        $this->assertEqualsWithDelta(275000.0, (float) $items['PPh21 TER A']->amount, 0.01);

        // Loan repayment line.
        $this->assertEqualsWithDelta(500000.0, (float) $items['Loan Repayment']->amount, 0.01);

        // Net = gross - (50k+100k+50k+275k+500k) = 5,500,000 - 975,000.
        $this->assertEqualsWithDelta(4525000.0, (float) $payroll->net_salary, 0.01);
    }

    public function test_it_applies_twenty_percent_surcharge_without_npwp(): void
    {
        $this->seedStatutoryConfig();

        $employee = Employee::factory()->create(['join_date' => now()->subYears(2)]);
        EmployeeTaxProfile::query()->create([
            'employee_id' => $employee->id,
            'has_npwp' => false,
            'tax_status' => 'TK/0',
            'tax_method' => 'ter_monthly',
            'dependents_count' => 0,
        ]);

        $payroll = app(PayrollCalculationService::class)->generate($employee, 3, 2026, 5000000.0);

        $line = $payroll->items->first(fn ($i) => str_contains($i->component_name, 'PPh21'));

        // 5% of 5,000,000 = 250,000, ×1.2 = 300,000.
        $this->assertNotNull($line);
        $this->assertStringContainsString('non-NPWP', $line->component_name);
        $this->assertEqualsWithDelta(300000.0, (float) $line->amount, 0.01);
    }

    public function test_it_adds_prorata_thr_in_june(): void
    {
        $this->seedStatutoryConfig();

        $employee = Employee::factory()->create(['join_date' => now()->setDate(2026, 1, 1)]);
        EmployeeTaxProfile::query()->create([
            'employee_id' => $employee->id,
            'has_npwp' => true,
            'tax_status' => 'TK/0',
            'tax_method' => 'ter_monthly',
            'dependents_count' => 0,
        ]);

        $payroll = app(PayrollCalculationService::class)->generate($employee, 6, 2026, 6000000.0);

        $thr = $payroll->items->first(fn ($i) => str_contains($i->component_name, 'THR'));
        $this->assertNotNull($thr, 'Expected a THR earning line in June.');
        // Joined Jan 2026, June period → 6 months worked → 6/12 × 6,000,000 = 3,000,000.
        $this->assertEqualsWithDelta(3000000.0, (float) $thr->amount, 0.01);
        $this->assertSame('earning', $thr->type);
    }
}

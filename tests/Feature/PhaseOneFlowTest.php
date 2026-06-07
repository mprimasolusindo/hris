<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BpjsConfig;
use App\Models\Payroll;
use App\Models\TaxRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseOneFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_create_flow_works(): void
    {
        $this->actingAs(User::factory()->create());

        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Phase One',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('employees.store'), [
            'company_id' => $companyId,
            'employee_code' => 'EMP-001',
            'full_name' => 'Andi Saputra',
            'email' => 'andi@example.test',
            'phone' => '081234567890',
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertTrue(
            str_contains($response->headers->get('Location'), '/employees/')
        );
        $this->assertDatabaseHas('emp_employees', [
            'employee_code' => 'EMP-001',
            'full_name' => 'Andi Saputra',
        ]);
    }

    public function test_attendance_capture_and_listing_works(): void
    {
        $this->actingAs(User::factory()->create());

        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Attendance',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $siteId = \DB::table('org_sites')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Jakarta Site',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employeeId = \DB::table('emp_employees')->insertGetId([
            'tenant_id' => null,
            'company_id' => $companyId,
            'employee_code' => 'EMP-ATT-1',
            'full_name' => 'Budi',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clockIn = now()->startOfDay()->addHours(8);
        $clockOut = now()->startOfDay()->addHours(17);

        $storeResponse = $this->post(route('attendance.store'), [
            'employee_id' => $employeeId,
            'site_id' => $siteId,
            'clock_in' => $clockIn->toDateTimeString(),
            'clock_out' => $clockOut->toDateTimeString(),
            'status' => 'present',
        ]);

        $storeResponse->assertRedirect(route('attendance.index'));
        $this->assertDatabaseHas('att_attendances', [
            'employee_id' => $employeeId,
            'status' => 'present',
        ]);

        $listResponse = $this->get(route('attendance.index', ['date' => now()->toDateString()]));
        $listResponse->assertOk();
    }

    public function test_payroll_generation_creates_payroll_and_items(): void
    {
        $this->actingAs(User::factory()->create());

        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Payroll',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employeeId = \DB::table('emp_employees')->insertGetId([
            'tenant_id' => null,
            'company_id' => $companyId,
            'employee_code' => 'EMP-PAY-1',
            'full_name' => 'Citra',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BpjsConfig::query()->create([
            'type' => 'kesehatan',
            'employee_percentage' => 0.0100,
            'company_percentage' => 0.0400,
        ]);

        TaxRule::query()->create([
            'name' => 'ter_monthly_A_phase_one',
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

        Attendance::query()->create([
            'employee_id' => $employeeId,
            'site_id' => null,
            'clock_in' => now()->startOfMonth()->addHours(8),
            'clock_out' => now()->startOfMonth()->addHours(17),
            'status' => 'present',
        ]);

        $month = (int) now()->month;
        $year = (int) now()->year;

        $response = $this->post(route('payroll.store'), [
            'employee_id' => $employeeId,
            'period_month' => $month,
            'period_year' => $year,
            'base_salary' => 10000000,
        ]);

        $payroll = Payroll::query()->firstOrFail();

        $response->assertRedirect(route('payroll.show', $payroll));
        $this->assertGreaterThanOrEqual(3, $payroll->items()->count());
        $this->assertDatabaseHas('pay_payrolls', [
            'employee_id' => $employeeId,
            'period_month' => $month,
            'period_year' => $year,
            'status' => 'generated',
        ]);
    }

    public function test_payroll_bulk_workflow_transition_works(): void
    {
        $this->actingAs(User::factory()->create());

        $companyId = \DB::table('org_companies')->insertGetId([
            'tenant_id' => null,
            'name' => 'PT Workflow',
            'type' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employeeId = \DB::table('emp_employees')->insertGetId([
            'tenant_id' => null,
            'company_id' => $companyId,
            'employee_code' => 'EMP-WF-1',
            'full_name' => 'Workflow User',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payrollId = \DB::table('pay_payrolls')->insertGetId([
            'employee_id' => $employeeId,
            'period_month' => (int) now()->month,
            'period_year' => (int) now()->year,
            'gross_salary' => 1000000,
            'total_deduction' => 50000,
            'net_salary' => 950000,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('payroll.bulk-update'), [
            'payroll_ids' => [$payrollId],
            'action' => 'reviewed',
            'approval_notes' => 'Checked by payroll admin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pay_payrolls', [
            'id' => $payrollId,
            'status' => 'reviewed',
            'approval_notes' => 'Checked by payroll admin',
        ]);
    }
}


<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\LeaveType;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreOpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_overtime_index_is_accessible(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('overtime.index'))->assertOk();
    }

    public function test_overtime_can_be_created_approved_and_deleted(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('overtime.store'), [
                'employee_id' => $employee->id,
                'date' => now()->toDateString(),
                'hours' => 3.5,
            ])
            ->assertRedirect(route('overtime.index'));

        $overtime = Overtime::query()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertSame('pending', $overtime->status);

        $this->actingAs($user)
            ->put(route('overtime.update', $overtime), ['status' => 'approved'])
            ->assertRedirect(route('overtime.index'));

        $this->assertSame('approved', $overtime->fresh()->status);

        $this->actingAs($user)
            ->delete(route('overtime.destroy', $overtime))
            ->assertRedirect(route('overtime.index'));

        $this->assertSoftDeleted($overtime);
    }

    public function test_employee_loan_crud(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('employees.loans.store', $employee), [
                'amount' => 10000000,
                'monthly_deduction' => 1000000,
            ])
            ->assertRedirect();

        $loan = EmployeeLoan::query()->where('employee_id', $employee->id)->firstOrFail();
        // remaining_amount defaults to amount when omitted.
        $this->assertEquals('10000000.00', $loan->remaining_amount);

        $this->actingAs($user)
            ->put(route('employees.loans.update', [$employee, $loan]), [
                'amount' => 10000000,
                'remaining_amount' => 8000000,
                'monthly_deduction' => 1000000,
            ])
            ->assertRedirect();

        $this->assertEquals('8000000.00', $loan->fresh()->remaining_amount);

        $this->actingAs($user)
            ->delete(route('employees.loans.destroy', [$employee, $loan]))
            ->assertRedirect();

        $this->assertSoftDeleted($loan);
    }

    public function test_leave_type_crud_and_balance_uses_entitlement(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('leave.types.store'), [
                'code' => 'annual',
                'name' => 'Annual Leave',
                'annual_entitlement_days' => 12,
                'is_paid' => true,
            ])
            ->assertRedirect(route('leave.types.index'));

        $type = LeaveType::query()->where('code', 'annual')->firstOrFail();

        $this->actingAs($user)
            ->put(route('leave.types.update', $type), [
                'code' => 'annual',
                'name' => 'Annual Leave (Cuti Tahunan)',
                'annual_entitlement_days' => 14,
                'is_paid' => true,
            ])
            ->assertRedirect(route('leave.types.index'));

        $this->assertSame(14, $type->fresh()->annual_entitlement_days);

        $this->actingAs($user)->get(route('leave.balance.index'))->assertOk();

        $this->actingAs($user)
            ->delete(route('leave.types.destroy', $type))
            ->assertRedirect(route('leave.types.index'));

        $this->assertSoftDeleted($type);
    }

    public function test_leave_store_accepts_db_type_codes(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        LeaveType::query()->create([
            'code' => 'cuti_khusus',
            'name' => 'Cuti Khusus',
            'annual_entitlement_days' => 3,
            'is_paid' => true,
        ]);

        $this->actingAs($user)
            ->post(route('leave.store'), [
                'employee_id' => $employee->id,
                'type' => 'cuti_khusus',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDay()->toDateString(),
            ])
            ->assertRedirect(route('leave.index'));

        $this->assertDatabaseHas('lv_leaves', [
            'employee_id' => $employee->id,
            'type' => 'cuti_khusus',
        ]);
    }

    public function test_employee_job_history_crud(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('employees.jobs.store', $employee), [
                'company_id' => $employee->company_id,
                'start_date' => now()->subYear()->toDateString(),
                'employment_type' => 'pkwtt',
            ])
            ->assertRedirect();

        $job = $employee->jobs()->firstOrFail();
        $this->assertSame('pkwtt', $job->employment_type);

        $this->actingAs($user)
            ->put(route('employees.jobs.update', [$employee, $job]), [
                'company_id' => $employee->company_id,
                'start_date' => now()->subYear()->toDateString(),
                'end_date' => now()->toDateString(),
                'employment_type' => 'pkwt',
            ])
            ->assertRedirect();

        $this->assertSame('pkwt', $job->fresh()->employment_type);

        $this->actingAs($user)
            ->delete(route('employees.jobs.destroy', [$employee, $job]))
            ->assertRedirect();

        $this->assertSoftDeleted($job);
    }

    public function test_employee_site_assignment_crud(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();
        $site = Site::factory()->create();

        $this->actingAs($user)
            ->post(route('employees.site-assignments.store', $employee), [
                'site_id' => $site->id,
                'start_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $assignment = $employee->siteAssignments()->firstOrFail();

        $this->actingAs($user)
            ->delete(route('employees.site-assignments.destroy', [$employee, $assignment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('rel_employee_sites', ['id' => $assignment->id]);
    }

    public function test_attendance_can_be_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'present',
        ]);

        $this->actingAs($user)
            ->put(route('attendance.update', $attendance), [
                'clock_in' => now()->setTime(9, 0)->toDateTimeString(),
                'status' => 'late',
            ])
            ->assertRedirect();

        $this->assertSame('late', $attendance->fresh()->status);

        $this->actingAs($user)
            ->delete(route('attendance.destroy', $attendance))
            ->assertRedirect();

        $this->assertDatabaseMissing('att_attendances', ['id' => $attendance->id]);
    }

    public function test_emergency_contact_crud(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('employees.emergency-contacts.store', $employee), [
                'name' => 'Budi',
                'relationship' => 'spouse',
                'phone' => '081234567890',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('emp_emergency_contacts', [
            'employee_id' => $employee->id,
            'name' => 'Budi',
        ]);
    }

    public function test_payroll_update_sets_approval_notes(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();
        $payroll = Payroll::query()->create([
            'employee_id' => $employee->id,
            'period_month' => 1,
            'period_year' => now()->year,
            'gross_salary' => 5000000,
            'total_deduction' => 500000,
            'net_salary' => 4500000,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->put(route('payroll.update', $payroll), [
                'approval_notes' => 'Reviewed and adjusted.',
                'status' => 'reviewed',
            ])
            ->assertRedirect(route('payroll.show', $payroll));

        $this->assertSame('Reviewed and adjusted.', $payroll->fresh()->approval_notes);
        $this->assertSame('reviewed', $payroll->fresh()->status);
    }
}

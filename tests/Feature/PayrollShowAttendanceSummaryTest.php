<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollShowAttendanceSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_summary_overtime_uses_approved_overtime_records(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $periodMonth = 3;
        $periodYear = 2026;

        $payroll = Payroll::query()->create([
            'employee_id' => $employee->id,
            'period_month' => $periodMonth,
            'period_year' => $periodYear,
            'gross_salary' => 5000000,
            'total_deduction' => 0,
            'net_salary' => 5000000,
            'status' => 'draft',
        ]);

        // Punches imply zero OT (exactly 8 hours); heuristic would still be wrong if OT claims exist.
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'clock_in' => sprintf('%d-%02d-10 08:00:00', $periodYear, $periodMonth),
            'clock_out' => sprintf('%d-%02d-10 16:00:00', $periodYear, $periodMonth),
            'status' => 'present',
        ]);

        Overtime::query()->create([
            'employee_id' => $employee->id,
            'date' => sprintf('%d-%02d-11', $periodYear, $periodMonth),
            'hours' => 3.5,
            'status' => 'approved',
        ]);
        Overtime::query()->create([
            'employee_id' => $employee->id,
            'date' => sprintf('%d-%02d-12', $periodYear, $periodMonth),
            'hours' => 2.0,
            'status' => 'approved',
        ]);
        Overtime::query()->create([
            'employee_id' => $employee->id,
            'date' => sprintf('%d-%02d-13', $periodYear, $periodMonth),
            'hours' => 10,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('payroll.show', $payroll))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('attendanceSummary.overtime_hours', 5.5)
            );
    }
}

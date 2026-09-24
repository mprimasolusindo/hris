<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollIndexSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_summary_counts_without_sql_error(): void
    {
        $this->actingAs(User::factory()->create());

        $employee = Employee::factory()->create();
        $year = (int) now()->year;

        Payroll::query()->create([
            'employee_id' => $employee->id,
            'period_month' => 1,
            'period_year' => $year,
            'gross_salary' => 5000000,
            'total_deduction' => 0,
            'net_salary' => 5000000,
            'status' => 'draft',
        ]);

        Payroll::query()->create([
            'employee_id' => $employee->id,
            'period_month' => 2,
            'period_year' => $year,
            'gross_salary' => 6000000,
            'total_deduction' => 500000,
            'net_salary' => 5500000,
            'status' => 'generated',
        ]);

        Payroll::query()->create([
            'employee_id' => $employee->id,
            'period_month' => 3,
            'period_year' => $year,
            'gross_salary' => 7000000,
            'total_deduction' => 0,
            'net_salary' => 7000000,
            'status' => 'paid',
        ]);

        $this->get(route('payroll.index', ['year' => $year]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('summary')
                ->where('summary.draft', 1)
                ->where('summary.generated', 1)
                ->where('summary.paid', 1)
                ->where('summary.total_amount', 17500000)
            );
    }
}

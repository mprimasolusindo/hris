<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_reminders_include_pending_leave_count(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        Leave::query()->create([
            'employee_id' => $employee->id,
            'type' => 'annual',
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('reminders')
                ->where('reminders.pendingLeaveCount', 1)
            );
    }

    public function test_authenticated_fallback_renders_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/this-route-does-not-exist')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('NotFound'));
    }

    public function test_payroll_show_includes_payslip_fields(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('payroll.store'), [
                'employee_id' => $employee->id,
                'period_month' => now()->month,
                'period_year' => now()->year,
                'base_salary' => 5000000,
            ]);

        $payroll = \App\Models\Payroll::query()->first();
        $this->assertNotNull($payroll);

        $this->actingAs($user)
            ->get(route('payroll.show', $payroll))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('payroll.period_label')
                ->has('payroll.company_name')
            );
    }
}

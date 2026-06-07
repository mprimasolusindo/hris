<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\Leave;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_workforce_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('shifts.index'))->assertOk();
        $this->actingAs($user)->get(route('shifts.calendar'))->assertOk();
        $this->actingAs($user)->get(route('shifts.assign'))->assertOk();
        $this->actingAs($user)->get(route('leave.index'))->assertOk();
        $this->actingAs($user)->get(route('leave.approvals.index'))->assertOk();
        $this->actingAs($user)->get(route('leave.balance.index'))->assertOk();
        $this->actingAs($user)->get(route('leave.types.index'))->assertOk();
        $this->actingAs($user)->get(route('contracts.index'))->assertOk();
    }

    public function test_shift_crud_and_assignment(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('shifts.store'), [
                'name' => 'Morning',
                'start_time' => '08:00',
                'end_time' => '16:00',
            ])
            ->assertRedirect(route('shifts.index'));

        $shift = Shift::query()->where('name', 'Morning')->first();
        $this->assertNotNull($shift);

        $this->actingAs($user)
            ->get(route('shifts.show', $shift))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('shifts.assign.store'), [
                'shift_id' => $shift->id,
                'date' => now()->toDateString(),
                'employee_ids' => [$employee->id],
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->put(route('shifts.update', $shift), [
                'name' => 'Morning Updated',
                'start_time' => '07:00',
                'end_time' => '15:00',
            ])
            ->assertRedirect(route('shifts.index'));

        $this->assertSame('Morning Updated', $shift->fresh()->name);
    }

    public function test_leave_request_and_approval(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('leave.store'), [
                'employee_id' => $employee->id,
                'type' => 'annual',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
            ])
            ->assertRedirect(route('leave.index'));

        $leave = Leave::query()->where('employee_id', $employee->id)->first();
        $this->assertNotNull($leave);
        $this->assertSame('pending', $leave->status);

        $this->actingAs($user)
            ->patch(route('leave.approvals.decide', $leave), ['decision' => 'approved'])
            ->assertRedirect(route('leave.approvals.index'));

        $this->assertSame('approved', $leave->fresh()->status);
    }

    public function test_contract_crud(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $this->actingAs($user)
            ->post(route('contracts.store'), [
                'employee_id' => $employee->id,
                'contract_type' => 'pkwt',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'salary_base' => 5000000,
            ])
            ->assertRedirect();

        $contract = EmploymentContract::query()->where('employee_id', $employee->id)->first();
        $this->assertNotNull($contract);

        $this->actingAs($user)
            ->get(route('contracts.show', $contract))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('contracts.update', $contract), [
                'employee_id' => $employee->id,
                'contract_type' => 'pkwtt',
                'start_date' => $contract->start_date->toDateString(),
                'end_date' => null,
                'salary_base' => 6000000,
            ])
            ->assertRedirect(route('contracts.show', $contract));

        $this->assertSame('pkwtt', $contract->fresh()->contract_type);

        $this->actingAs($user)
            ->delete(route('contracts.destroy', $contract))
            ->assertRedirect(route('contracts.index'));

        $this->assertSoftDeleted($contract);
    }
}

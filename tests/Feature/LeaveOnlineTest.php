<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveOnlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_ess_employee_submits_own_leave_with_reason(): void
    {
        $user = $this->createUserWithRole('employee');
        $otherUser = User::factory()->create();
        $mine = Employee::factory()->create(['user_id' => $user->id]);
        $other = Employee::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user)
            ->post(route('leave.store'), [
                'employee_id' => $other->id, // should be forced to self
                'type' => 'permission',
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'reason' => 'Mengurus KTP ke Disdukcapil',
            ])
            ->assertRedirect(route('leave.index'));

        $leave = Leave::query()->where('employee_id', $mine->id)->first();
        $this->assertNotNull($leave);
        $this->assertSame('permission', $leave->type);
        $this->assertSame('pending', $leave->status);
        $this->assertSame('Mengurus KTP ke Disdukcapil', $leave->reason);
        $this->assertSame(0, Leave::query()->where('employee_id', $other->id)->count());
    }

    public function test_ess_leave_index_is_scoped_to_own_records(): void
    {
        $user = $this->createUserWithRole('employee');
        $otherUser = User::factory()->create();
        $mine = Employee::factory()->create(['user_id' => $user->id]);
        $other = Employee::factory()->create(['user_id' => $otherUser->id]);

        Leave::query()->create([
            'employee_id' => $mine->id,
            'type' => 'annual',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'reason' => 'Mine',
            'status' => 'pending',
        ]);
        Leave::query()->create([
            'employee_id' => $other->id,
            'type' => 'annual',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'reason' => 'Other',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('leave.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Leave/Index')
                ->where('selfService', true)
                ->has('leaves.data', 1)
                ->where('leaves.data.0.reason', 'Mine'));
    }

    public function test_ess_can_cancel_own_pending_leave_only(): void
    {
        $user = $this->createUserWithRole('employee');
        $otherUser = User::factory()->create();
        $mine = Employee::factory()->create(['user_id' => $user->id]);
        $other = Employee::factory()->create(['user_id' => $otherUser->id]);

        $own = Leave::query()->create([
            'employee_id' => $mine->id,
            'type' => 'sick',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'status' => 'pending',
        ]);
        $foreign = Leave::query()->create([
            'employee_id' => $other->id,
            'type' => 'sick',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->patch(route('leave.cancel', $own))
            ->assertRedirect(route('leave.index'));
        $this->assertSame('cancelled', $own->fresh()->status);

        $this->actingAs($user)
            ->patch(route('leave.cancel', $foreign))
            ->assertForbidden();
        $this->assertSame('pending', $foreign->fresh()->status);
    }

    public function test_ess_without_linked_employee_cannot_submit(): void
    {
        $user = $this->createUserWithRole('employee');

        $this->actingAs($user)
            ->post(route('leave.store'), [
                'type' => 'annual',
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'reason' => 'No link',
            ])
            ->assertSessionHasErrors('employee_id');
    }

    public function test_manager_approval_records_decided_at(): void
    {
        $managerUser = $this->createUserWithRole('manager');
        $employeeUser = User::factory()->create();
        $managerEmployee = Employee::factory()->create(['user_id' => $managerUser->id]);
        $employee = Employee::factory()->create(['user_id' => $employeeUser->id]);

        $leave = Leave::query()->create([
            'employee_id' => $employee->id,
            'type' => 'annual',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Family event',
            'status' => 'pending',
        ]);

        $this->actingAs($managerUser)
            ->patch(route('leave.approvals.decide', $leave), ['decision' => 'approved'])
            ->assertRedirect(route('leave.approvals.index'));

        $leave->refresh();
        $this->assertSame('approved', $leave->status);
        $this->assertSame($managerEmployee->id, $leave->approved_by);
        $this->assertNotNull($leave->decided_at);
    }

    public function test_ess_balance_is_scoped_to_self(): void
    {
        $user = $this->createUserWithRole('employee');
        $otherUser = User::factory()->create();
        $mine = Employee::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Employee::factory()->create(['user_id' => $otherUser->id, 'status' => 'active']);

        $this->actingAs($user)
            ->get(route('leave.balance.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Leave/Balance')
                ->where('selfService', true)
                ->has('balances', 1)
                ->where('balances.0.employee_id', $mine->id));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_work_schedule_and_holiday_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('work-schedules.index'))->assertOk();
        $this->actingAs($user)->get(route('holidays.index'))->assertOk();
        $this->actingAs($user)->get(route('holidays.index', ['year' => 2026]))->assertOk();
    }

    public function test_work_schedule_crud_and_default_flag_exclusivity(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('work-schedules.store'), [
                'name' => 'Office 5D',
                'code' => 'OFF-5D',
                'is_default' => true,
                'default_start_time' => '08:00',
                'default_end_time' => '17:00',
                'mon_working' => true,
                'tue_working' => true,
                'wed_working' => true,
                'thu_working' => true,
                'fri_working' => true,
                'sat_working' => false,
                'sun_working' => false,
            ])
            ->assertRedirect(route('work-schedules.index'));

        $first = WorkSchedule::query()->where('code', 'OFF-5D')->first();
        $this->assertNotNull($first);
        $this->assertTrue($first->is_default);
        $this->assertTrue($first->isWorkingWeekday(1));
        $this->assertFalse($first->isWorkingWeekday(6));

        $this->actingAs($user)
            ->post(route('work-schedules.store'), [
                'name' => 'Ops 6D',
                'code' => 'OPS-6D',
                'is_default' => true,
                'default_start_time' => '08:00',
                'default_end_time' => '16:00',
                'mon_working' => true,
                'tue_working' => true,
                'wed_working' => true,
                'thu_working' => true,
                'fri_working' => true,
                'sat_working' => true,
                'sun_working' => false,
            ])
            ->assertRedirect(route('work-schedules.index'));

        $this->assertFalse($first->fresh()->is_default);
        $second = WorkSchedule::query()->where('code', 'OPS-6D')->first();
        $this->assertNotNull($second);
        $this->assertTrue($second->is_default);

        $this->actingAs($user)
            ->put(route('work-schedules.update', $second), [
                'name' => 'Ops 6D Updated',
                'code' => 'OPS-6D',
                'is_default' => true,
                'default_start_time' => '07:30',
                'default_end_time' => '15:30',
                'mon_working' => true,
                'tue_working' => true,
                'wed_working' => true,
                'thu_working' => true,
                'fri_working' => true,
                'sat_working' => true,
                'sun_working' => false,
            ])
            ->assertRedirect(route('work-schedules.index'));

        $this->assertSame('Ops 6D Updated', $second->fresh()->name);

        $this->actingAs($user)
            ->delete(route('work-schedules.destroy', $second))
            ->assertRedirect(route('work-schedules.index'));

        $this->assertNotNull($second->fresh());
        $this->assertTrue($second->fresh()->is_default);

        $this->actingAs($user)
            ->delete(route('work-schedules.destroy', $first))
            ->assertRedirect(route('work-schedules.index'));

        $this->assertSoftDeleted('att_work_schedules', ['id' => $first->id]);
    }

    public function test_work_schedule_code_must_be_unique(): void
    {
        $user = User::factory()->create();

        WorkSchedule::query()->create([
            'name' => 'Existing',
            'code' => 'DUP-01',
            'is_default' => false,
            'mon_working' => true,
            'tue_working' => true,
            'wed_working' => true,
            'thu_working' => true,
            'fri_working' => true,
            'sat_working' => false,
            'sun_working' => false,
        ]);

        $this->actingAs($user)
            ->post(route('work-schedules.store'), [
                'name' => 'Another',
                'code' => 'DUP-01',
                'is_default' => false,
                'mon_working' => true,
                'tue_working' => true,
                'wed_working' => true,
                'thu_working' => true,
                'fri_working' => true,
                'sat_working' => false,
                'sun_working' => false,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_holiday_crud_unique_date_and_type_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('holidays.store'), [
                'name' => 'Independence Day',
                'holiday_date' => '2026-08-17',
                'type' => 'national',
                'is_half_day' => false,
                'notes' => null,
            ])
            ->assertRedirect(route('holidays.index', ['year' => '2026']));

        $holiday = Holiday::query()->whereDate('holiday_date', '2026-08-17')->first();
        $this->assertNotNull($holiday);
        $this->assertSame('national', $holiday->type);

        $this->actingAs($user)
            ->post(route('holidays.store'), [
                'name' => 'Duplicate date',
                'holiday_date' => '2026-08-17',
                'type' => 'company',
            ])
            ->assertSessionHasErrors('holiday_date');

        $this->actingAs($user)
            ->post(route('holidays.store'), [
                'name' => 'Bad type',
                'holiday_date' => '2026-12-25',
                'type' => 'weekend',
            ])
            ->assertSessionHasErrors('type');

        $this->actingAs($user)
            ->put(route('holidays.update', $holiday), [
                'name' => 'Hari Kemerdekaan RI',
                'holiday_date' => '2026-08-17',
                'type' => 'national',
                'is_half_day' => false,
                'notes' => 'Demo',
            ])
            ->assertRedirect(route('holidays.index', ['year' => '2026']));

        $this->assertSame('Hari Kemerdekaan RI', $holiday->fresh()->name);

        $this->actingAs($user)
            ->delete(route('holidays.destroy', $holiday))
            ->assertRedirect(route('holidays.index', ['year' => 2026]));

        $this->assertSoftDeleted('att_holidays', ['id' => $holiday->id]);
    }

    public function test_employee_role_cannot_manage_work_schedules(): void
    {
        $user = $this->createUserWithRole('employee');

        $this->actingAs($user)
            ->get(route('work-schedules.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('holidays.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('work-schedules.store'), [
                'name' => 'Nope',
                'code' => 'NOPE',
                'is_default' => false,
                'mon_working' => true,
                'tue_working' => true,
                'wed_working' => true,
                'thu_working' => true,
                'fri_working' => true,
                'sat_working' => false,
                'sun_working' => false,
            ])
            ->assertForbidden();
    }
}

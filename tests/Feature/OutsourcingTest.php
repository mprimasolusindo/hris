<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\User;
use App\Models\VendorEmployee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutsourcingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_outsourcing_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('vendors.index'))->assertOk();
        $this->actingAs($user)->get(route('outsourcing.index'))->assertOk();
        $this->actingAs($user)->get(route('outsourcing.tracking.index'))->assertOk();
        $this->actingAs($user)->get(route('outsourcing.compliance.index'))->assertOk();
        $this->actingAs($user)->get(route('vendor-billing.index'))->assertOk();
    }

    public function test_vendor_and_placement_crud(): void
    {
        $user = User::factory()->create();
        $employer = Company::factory()->create(['type' => 'main']);
        $employee = Employee::factory()->create(['company_id' => $employer->id]);

        $this->actingAs($user)
            ->post(route('vendors.store'), ['name' => 'PT Vendor Alih Daya'])
            ->assertRedirect(route('vendors.index'));

        $vendor = Company::query()->where('name', 'PT Vendor Alih Daya')->first();
        $this->assertNotNull($vendor);
        $this->assertSame('vendor', $vendor->type);

        $this->actingAs($user)
            ->get(route('vendors.show', $vendor))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('outsourcing.store'), [
                'vendor_id' => $vendor->id,
                'employee_id' => $employee->id,
            ])
            ->assertRedirect(route('outsourcing.index'));

        $placement = VendorEmployee::query()
            ->where('vendor_id', $vendor->id)
            ->where('employee_id', $employee->id)
            ->first();
        $this->assertNotNull($placement);

        $this->actingAs($user)
            ->delete(route('outsourcing.destroy', $placement))
            ->assertRedirect(route('outsourcing.index'));

        $this->assertDatabaseMissing('rel_vendor_employees', ['id' => $placement->id]);
    }

    public function test_compliance_flags_missing_outsourcing_contract(): void
    {
        $user = User::factory()->create();
        $employer = Company::factory()->create(['type' => 'main']);
        $vendor = Company::factory()->create(['type' => 'vendor', 'name' => 'Vendor X']);
        $employee = Employee::factory()->create(['company_id' => $employer->id, 'status' => 'active']);

        VendorEmployee::query()->create([
            'vendor_id' => $vendor->id,
            'employee_id' => $employee->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('outsourcing.compliance.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Outsourcing/Compliance/Index')
            ->has('flags', 1)
            ->where('flags.0.type', 'missing_outsourcing_contract')
        );
    }

    public function test_compliance_no_flag_when_outsourcing_contract_exists(): void
    {
        $user = User::factory()->create();
        $employer = Company::factory()->create(['type' => 'main']);
        $vendor = Company::factory()->create(['type' => 'vendor']);
        $employee = Employee::factory()->create(['company_id' => $employer->id, 'status' => 'active']);

        VendorEmployee::query()->create([
            'vendor_id' => $vendor->id,
            'employee_id' => $employee->id,
        ]);

        EmploymentContract::query()->create([
            'employee_id' => $employee->id,
            'contract_type' => 'outsourcing',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'salary_base' => 5000000,
        ]);

        $this->actingAs($user)
            ->get(route('outsourcing.compliance.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Outsourcing/Compliance/Index')
                ->where('summary.total', 0)
            );
    }
}

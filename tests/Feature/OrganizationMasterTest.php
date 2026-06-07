<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_organization_and_master_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('organization.companies.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('organization.sites.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('master.allowance-types.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('payroll.master-allowances.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('payroll.master-deductions.index'))
            ->assertOk();
    }

    public function test_company_crud_via_http(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('organization.companies.store'), [
                'name' => 'PT Contoh',
                'type' => 'main',
            ])
            ->assertRedirect(route('organization.companies.index'));

        $company = Company::query()->where('name', 'PT Contoh')->first();
        $this->assertNotNull($company);

        $this->actingAs($user)
            ->put(route('organization.companies.update', $company), [
                'name' => 'PT Contoh Updated',
                'type' => 'vendor',
            ])
            ->assertRedirect(route('organization.companies.index'));

        $this->assertSame('PT Contoh Updated', $company->fresh()->name);

        $this->actingAs($user)
            ->delete(route('organization.companies.destroy', $company))
            ->assertRedirect(route('organization.companies.index'));

        $this->assertSoftDeleted($company);
    }

    public function test_salary_component_earning_and_deduction_separation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('master.allowance-types.store'), [
                'name' => 'Transport',
                'is_taxable' => true,
            ]);

        $this->actingAs($user)
            ->post(route('payroll.master-deductions.store'), [
                'name' => 'BPJS',
                'is_taxable' => false,
            ]);

        $this->assertDatabaseHas('cfg_salary_components', [
            'name' => 'Transport',
            'type' => 'earning',
        ]);

        $this->assertDatabaseHas('cfg_salary_components', [
            'name' => 'BPJS',
            'type' => 'deduction',
        ]);

        $earning = SalaryComponent::query()->where('name', 'Transport')->first();
        $this->actingAs($user)
            ->delete(route('payroll.master-deductions.destroy', $earning))
            ->assertNotFound();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeJob;
use App\Models\JobPosting;
use App\Models\Position;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentTalentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_recruitment_and_talent_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('recruitment.jobs.index'))->assertOk();
        $this->actingAs($user)->get(route('recruitment.candidates.index'))->assertOk();
        $this->actingAs($user)->get(route('recruitment.pipeline.index'))->assertOk();
        $this->actingAs($user)->get(route('recruitment.interviews.index'))->assertOk();
        $this->actingAs($user)->get(route('performance.index'))->assertOk();
        $this->actingAs($user)->get(route('training.index'))->assertOk();
        $this->actingAs($user)->get(route('talent-pool.index'))->assertOk();
        $this->actingAs($user)->get(route('succession.index'))->assertOk();
        $this->actingAs($user)->get(route('succession.nine-box.index'))->assertOk();
    }

    public function test_job_candidate_and_application_flow(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['type' => 'main']);

        $this->actingAs($user)
            ->post(route('recruitment.jobs.store'), [
                'company_id' => $company->id,
                'title' => 'Software Engineer',
                'code' => 'REQ-SE-001',
                'employment_type' => 'permanent',
                'priority' => 'high',
                'headcount' => 2,
                'status' => 'draft',
                'publish' => true,
                'description' => 'Build HRIS features',
                'requirements' => 'PHP, Laravel',
            ])
            ->assertRedirect();

        $job = JobPosting::query()->where('title', 'Software Engineer')->first();
        $this->assertNotNull($job);
        $this->assertSame('open', $job->status);
        $this->assertSame('REQ-SE-001', $job->code);
        $this->assertSame('high', $job->priority);
        $this->assertNotNull($job->opened_at);

        $this->actingAs($user)
            ->post(route('recruitment.candidates.store'), [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'phone' => '08123456789',
            ])
            ->assertRedirect();

        $candidate = Candidate::query()->where('email', 'budi@example.com')->first();
        $this->assertNotNull($candidate);

        $this->actingAs($user)
            ->post(route('recruitment.applications.store'), [
                'candidate_id' => $candidate->id,
                'job_id' => $job->id,
            ])
            ->assertRedirect(route('recruitment.pipeline.index'));

        $application = Application::query()
            ->where('candidate_id', $candidate->id)
            ->where('job_id', $job->id)
            ->first();
        $this->assertNotNull($application);
        $this->assertSame('applied', $application->stage);

        $this->actingAs($user)
            ->patch(route('recruitment.applications.stage', $application), [
                'stage' => 'offer',
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertSame('offer', $application->stage);

        $this->actingAs($user)
            ->post(route('recruitment.applications.hire', $application))
            ->assertRedirect();

        $application->refresh();
        $this->assertSame('hired', $application->stage);

        $employee = Employee::query()->where('email', 'budi@example.com')->first();
        $this->assertNotNull($employee);
        $this->assertSame($company->id, $employee->company_id);
        $this->assertSame('active', $employee->status);
    }

    public function test_job_and_candidate_crud(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['type' => 'main']);

        $job = JobPosting::query()->create([
            'company_id' => $company->id,
            'title' => 'HR Admin',
            'code' => 'REQ-HR-001',
            'employment_type' => 'permanent',
            'priority' => 'medium',
            'status' => 'open',
            'headcount' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('recruitment.jobs.show', $job))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('recruitment.jobs.update', $job), [
                'company_id' => $company->id,
                'title' => 'HR Specialist',
                'code' => 'REQ-HR-001',
                'employment_type' => 'contract',
                'priority' => 'high',
                'status' => 'on_hold',
                'headcount' => 1,
                'location_note' => 'Hybrid Jakarta',
                'salary_min' => 8000000,
                'salary_max' => 12000000,
                'currency' => 'IDR',
            ])
            ->assertRedirect(route('recruitment.jobs.show', $job));

        $job->refresh();
        $this->assertSame('HR Specialist', $job->title);
        $this->assertSame('contract', $job->employment_type);
        $this->assertSame('Hybrid Jakarta', $job->location_note);

        $candidate = Candidate::query()->create([
            'name' => 'Ani Wijaya',
            'email' => 'ani@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('recruitment.candidates.show', $candidate))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('recruitment.candidates.update', $candidate), [
                'name' => 'Ani Wijaya',
                'email' => 'ani.updated@example.com',
                'phone' => null,
            ])
            ->assertRedirect(route('recruitment.candidates.show', $candidate));

        $this->actingAs($user)
            ->delete(route('recruitment.jobs.destroy', $job))
            ->assertRedirect(route('recruitment.jobs.index'));

        $this->assertSoftDeleted('trx_jobs', ['id' => $job->id]);
    }

    public function test_hire_copies_job_posting_department_position_and_site(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['type' => 'main']);

        $firstDepartment = Department::factory()->create([
            'company_id' => $company->id,
            'name' => 'First Department',
        ]);
        $jobDepartment = Department::factory()->create([
            'company_id' => $company->id,
            'name' => 'Job Department',
        ]);

        $firstPosition = Position::factory()->create(['name' => 'First Position']);
        $jobPosition = Position::factory()->create(['name' => 'Job Position']);

        $firstSite = Site::factory()->create([
            'company_id' => $company->id,
            'name' => 'First Site',
        ]);
        $jobSite = Site::factory()->create([
            'company_id' => $company->id,
            'name' => 'Job Site',
        ]);

        $job = JobPosting::query()->create([
            'company_id' => $company->id,
            'department_id' => $jobDepartment->id,
            'position_id' => $jobPosition->id,
            'site_id' => $jobSite->id,
            'title' => 'Target Role',
            'code' => 'REQ-TARGET-001',
            'employment_type' => 'permanent',
            'priority' => 'medium',
            'status' => 'open',
            'headcount' => 1,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Hire Org Test',
            'email' => 'hire-org@example.com',
        ]);

        $application = Application::query()->create([
            'candidate_id' => $candidate->id,
            'job_id' => $job->id,
            'stage' => 'offer',
        ]);

        $this->actingAs($user)
            ->post(route('recruitment.applications.hire', $application))
            ->assertRedirect();

        $employee = Employee::query()->where('email', 'hire-org@example.com')->first();
        $this->assertNotNull($employee);

        $employeeJob = EmployeeJob::query()->where('employee_id', $employee->id)->first();
        $this->assertNotNull($employeeJob);
        $this->assertSame($jobDepartment->id, $employeeJob->department_id);
        $this->assertSame($jobPosition->id, $employeeJob->position_id);
        $this->assertNotSame($firstDepartment->id, $employeeJob->department_id);
        $this->assertNotSame($firstPosition->id, $employeeJob->position_id);

        $this->assertDatabaseHas('rel_employee_sites', [
            'employee_id' => $employee->id,
            'site_id' => $jobSite->id,
        ]);
        $this->assertDatabaseMissing('rel_employee_sites', [
            'employee_id' => $employee->id,
            'site_id' => $firstSite->id,
        ]);
    }
}

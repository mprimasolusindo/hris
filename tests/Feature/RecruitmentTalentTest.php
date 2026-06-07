<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Employee;
use App\Models\JobPosting;
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
                'status' => 'open',
            ])
            ->assertRedirect();

        $job = JobPosting::query()->where('title', 'Software Engineer')->first();
        $this->assertNotNull($job);

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
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get(route('recruitment.jobs.show', $job))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('recruitment.jobs.update', $job), [
                'company_id' => $company->id,
                'title' => 'HR Specialist',
                'status' => 'on_hold',
            ])
            ->assertRedirect(route('recruitment.jobs.show', $job));

        $job->refresh();
        $this->assertSame('HR Specialist', $job->title);

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
}

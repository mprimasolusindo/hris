<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeJob;
use App\Models\EmployeeSite;
use App\Models\EmploymentContract;
use App\Models\JobPosting;
use App\Models\Site;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PipelineController extends Controller
{
    public const STAGES = ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'];

    public function index(Request $request): Response
    {
        $jobId = (string) $request->query('job_id', '');

        $applications = Application::query()
            ->with([
                'candidate:id,name,email',
                'jobPosting:id,title,company_id',
                'jobPosting.company:id,name',
            ])
            ->when($jobId !== '', fn ($q) => $q->where('job_id', $jobId))
            ->latest()
            ->get()
            ->map(fn (Application $app) => [
                'id' => $app->id,
                'stage' => $app->stage,
                'candidate_name' => $app->candidate?->name,
                'candidate_email' => $app->candidate?->email,
                'job_title' => $app->jobPosting?->title,
                'company_name' => $app->jobPosting?->company?->name,
                'updated_at' => $app->updated_at?->toDateTimeString(),
            ]);

        $byStage = collect(self::STAGES)->mapWithKeys(function (string $stage) use ($applications) {
            return [$stage => $applications->where('stage', $stage)->values()];
        });

        $perPage = ListPaginator::resolvePerPage($request);
        $applications = ListPaginator::paginateCollection($applications, $request);

        return Inertia::render('Recruitment/Pipeline/Index', [
            'stages' => self::STAGES,
            'board' => $byStage,
            'applications' => $applications,
            'filters' => ['job_id' => $jobId, 'per_page' => (string) $perPage],
            'jobs' => JobPosting::query()->orderBy('title')->get(['id', 'title']),
            'candidates' => Candidate::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'candidate_id' => ['required', 'exists:trx_candidates,id'],
            'job_id' => ['required', 'exists:trx_jobs,id'],
            'stage' => ['nullable', 'string', 'in:'.implode(',', self::STAGES)],
        ]);

        Application::query()->updateOrCreate(
            [
                'candidate_id' => $data['candidate_id'],
                'job_id' => $data['job_id'],
            ],
            ['stage' => $data['stage'] ?? 'applied'],
        );

        return redirect()
            ->route('recruitment.pipeline.index')
            ->with('success', 'Application added to pipeline.');
    }

    public function updateStage(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate([
            'stage' => ['required', 'string', 'in:'.implode(',', self::STAGES)],
        ]);

        $application->update(['stage' => $data['stage']]);

        return redirect()
            ->back()
            ->with('success', 'Stage updated.');
    }

    public function hire(Application $application): RedirectResponse
    {
        $application->load(['candidate', 'jobPosting']);

        $candidate = $application->candidate;
        $job = $application->jobPosting;

        if (! $candidate || ! $job) {
            return redirect()
                ->route('recruitment.pipeline.index')
                ->with('success', 'Application is incomplete.');
        }

        $code = 'EMP-'.Str::upper(Str::random(6));
        $joinDate = now();

        // Default starting salary when the job posting carries none (IDR).
        $salaryBase = 7_000_000;

        $employee = DB::transaction(function () use ($job, $candidate, $code, $joinDate, $salaryBase) {
            $employee = Employee::query()->create([
                'company_id' => $job->company_id,
                'employee_code' => $code,
                'full_name' => $candidate->name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'status' => 'active',
                'join_date' => $joinDate,
            ]);

            // Permanent employment agreement (PKWTT) — no fixed end date.
            EmploymentContract::query()->create([
                'employee_id' => $employee->id,
                'contract_type' => 'pkwtt',
                'start_date' => $joinDate->toDateString(),
                'end_date' => null,
                'salary_base' => $salaryBase,
            ]);

            $departmentId = $job->department_id
                ?? Department::query()->where('company_id', $job->company_id)->orderBy('id')->value('id');

            $positionId = $job->position_id;

            EmployeeJob::query()->create([
                'employee_id' => $employee->id,
                'company_id' => $job->company_id,
                'department_id' => $departmentId,
                'position_id' => $positionId,
                'employment_type' => 'pkwtt',
                'start_date' => $joinDate->toDateString(),
                'end_date' => null,
            ]);

            $siteId = $job->site_id
                ?? Site::query()->where('company_id', $job->company_id)->orderBy('id')->value('id');

            if ($siteId !== null) {
                EmployeeSite::query()->create([
                    'employee_id' => $employee->id,
                    'site_id' => $siteId,
                    'start_date' => $joinDate->toDateString(),
                    'end_date' => null,
                ]);
            }

            return $employee;
        });

        $application->update(['stage' => 'hired']);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Candidate hired: employee, contract, job, and site records created.');
    }
}

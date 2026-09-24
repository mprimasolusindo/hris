<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosting;
use App\Models\Position;
use App\Models\Site;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JobPostingController extends Controller
{
    public const STATUSES = ['draft', 'open', 'on_hold', 'closed', 'filled', 'cancelled'];

    public const EMPLOYMENT_TYPES = ['permanent', 'contract', 'outsourced', 'internship'];

    public const PRIORITIES = ['low', 'medium', 'high'];

    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');
        $companyId = (string) $request->query('company_id', '');

        $query = JobPosting::query()
            ->with('company:id,name')
            ->withCount('applications')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($companyId !== '', fn ($q) => $q->where('company_id', $companyId))
            ->latest();

        $totalCount = (clone $query)->count();
        $openCount = (clone $query)->where('status', 'open')->count();
        $perPage = ListPaginator::resolvePerPage($request);
        $jobs = ListPaginator::paginate($query, $request);
        $jobs->getCollection()->transform(fn (JobPosting $job) => [
            'id' => $job->id,
            'title' => $job->title,
            'code' => $job->code,
            'status' => $job->status,
            'priority' => $job->priority,
            'employment_type' => $job->employment_type,
            'headcount' => $job->headcount,
            'company_name' => $job->company?->name,
            'application_count' => $job->applications_count,
            'created_at' => $job->created_at?->toDateTimeString(),
        ]);

        return Inertia::render('Recruitment/Jobs/Index', [
            'jobs' => $jobs,
            'filters' => [
                'status' => $status,
                'company_id' => $companyId,
                'per_page' => (string) $perPage,
            ],
            'summary' => [
                'open' => $openCount,
                'total' => $totalCount,
            ],
            'statusOptions' => self::STATUSES,
            'formOptions' => $this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $job = JobPosting::query()->create($data);

        return redirect()
            ->route('recruitment.jobs.show', $job)
            ->with('success', 'Job posting created.');
    }

    public function show(JobPosting $jobPosting): Response
    {
        $jobPosting->load([
            'company:id,name',
            'applications.candidate:id,name,email,phone',
        ]);

        return Inertia::render('Recruitment/Jobs/Show', [
            'job' => $this->serializeJob($jobPosting),
            'applications' => $jobPosting->applications->map(fn ($app) => [
                'id' => $app->id,
                'candidate_name' => $app->candidate?->name,
                'candidate_email' => $app->candidate?->email,
                'stage' => $app->stage,
                'created_at' => $app->created_at?->toDateTimeString(),
            ]),
            'statusOptions' => self::STATUSES,
            'formOptions' => $this->formOptions(),
        ]);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $data = $this->validated($request, $jobPosting);
        $jobPosting->update($data);

        return redirect()
            ->route('recruitment.jobs.show', $jobPosting)
            ->with('success', 'Job posting updated.');
    }

    public function destroy(JobPosting $jobPosting): RedirectResponse
    {
        $jobPosting->delete();

        return redirect()
            ->route('recruitment.jobs.index')
            ->with('success', 'Job posting deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name', 'company_id']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name', 'company_id']),
            'positions' => Position::query()->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'employee_code']),
            'employmentTypes' => self::EMPLOYMENT_TYPES,
            'priorities' => self::PRIORITIES,
            'statusOptions' => self::STATUSES,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeJob(JobPosting $job): array
    {
        return [
            'id' => $job->id,
            'company_id' => $job->company_id,
            'company_name' => $job->company?->name,
            'site_id' => $job->site_id,
            'department_id' => $job->department_id,
            'position_id' => $job->position_id,
            'title' => $job->title,
            'code' => $job->code,
            'employment_type' => $job->employment_type ?? 'permanent',
            'headcount' => $job->headcount ?? 1,
            'filled_count' => $job->filled_count ?? 0,
            'status' => $job->status,
            'priority' => $job->priority ?? 'medium',
            'opened_at' => $job->opened_at?->toDateString(),
            'target_close_date' => $job->target_close_date?->toDateString(),
            'closed_at' => $job->closed_at?->toDateTimeString(),
            'hiring_manager_id' => $job->hiring_manager_id,
            'recruiter_id' => $job->recruiter_id,
            'salary_min' => $job->salary_min !== null ? (string) $job->salary_min : null,
            'salary_max' => $job->salary_max !== null ? (string) $job->salary_max : null,
            'currency' => $job->currency ?: 'IDR',
            'description' => $job->description,
            'requirements' => $job->requirements,
            'benefits' => $job->benefits,
            'location_note' => $job->location_note,
            'created_at' => $job->created_at?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?JobPosting $job = null): array
    {
        // Empty optional FKs / numbers → null for nullable columns.
        $nullable = [
            'site_id', 'department_id', 'position_id',
            'hiring_manager_id', 'recruiter_id',
            'opened_at', 'target_close_date',
            'salary_min', 'salary_max',
            'description', 'requirements', 'benefits', 'location_note',
        ];
        foreach ($nullable as $key) {
            if ($request->input($key) === '' || $request->input($key) === null) {
                $request->merge([$key => null]);
            }
        }

        $publish = $request->boolean('publish');

        $data = $request->validate([
            'company_id' => ['required', 'exists:org_companies,id'],
            'site_id' => ['nullable', 'exists:org_sites,id'],
            'department_id' => ['nullable', 'exists:org_departments,id'],
            'position_id' => ['nullable', 'exists:org_positions,id'],
            'title' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('trx_jobs', 'code')
                    ->where(fn ($q) => $q->where('company_id', $request->input('company_id')))
                    ->ignore($job?->id),
            ],
            'employment_type' => ['required', 'string', Rule::in(self::EMPLOYMENT_TYPES)],
            'headcount' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'priority' => ['required', 'string', Rule::in(self::PRIORITIES)],
            'opened_at' => ['nullable', 'date'],
            'target_close_date' => ['nullable', 'date', 'after_or_equal:opened_at'],
            'hiring_manager_id' => ['nullable', 'exists:emp_employees,id'],
            'recruiter_id' => ['nullable', 'exists:emp_employees,id'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'currency' => ['nullable', 'string', 'max:8'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'location_note' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(self::STATUSES)],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $status = $publish
            ? 'open'
            : ($data['status'] ?? $job?->status ?? 'draft');

        $openedAt = $data['opened_at'] ?? null;
        if ($publish && blank($openedAt)) {
            $openedAt = now()->toDateString();
        }

        return [
            'company_id' => $data['company_id'],
            'site_id' => $data['site_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'title' => $data['title'],
            'code' => $data['code'],
            'employment_type' => $data['employment_type'],
            'headcount' => (int) ($data['headcount'] ?? 1),
            'status' => $status,
            'priority' => $data['priority'],
            'opened_at' => $openedAt,
            'target_close_date' => $data['target_close_date'] ?? null,
            'hiring_manager_id' => $data['hiring_manager_id'] ?? null,
            'recruiter_id' => $data['recruiter_id'] ?? null,
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'currency' => $data['currency'] ?? 'IDR',
            'description' => $data['description'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'benefits' => $data['benefits'] ?? null,
            'location_note' => $data['location_note'] ?? null,
        ];
    }
}

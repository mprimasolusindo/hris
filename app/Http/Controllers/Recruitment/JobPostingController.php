<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobPostingController extends Controller
{
    public const STATUSES = ['open', 'on_hold', 'closed', 'filled'];

    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');
        $companyId = (string) $request->query('company_id', '');

        $jobs = JobPosting::query()
            ->with('company:id,name')
            ->withCount('applications')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($companyId !== '', fn ($q) => $q->where('company_id', $companyId))
            ->latest()
            ->get()
            ->map(fn (JobPosting $job) => [
                'id' => $job->id,
                'title' => $job->title,
                'status' => $job->status,
                'company_name' => $job->company?->name,
                'application_count' => $job->applications_count,
                'created_at' => $job->created_at?->toDateTimeString(),
            ]);

        $openCount = $jobs->where('status', 'open')->count();

        return Inertia::render('Recruitment/Jobs/Index', [
            'jobs' => $jobs,
            'filters' => ['status' => $status, 'company_id' => $companyId],
            'summary' => [
                'open' => $openCount,
                'total' => $jobs->count(),
            ],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => self::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:org_companies,id'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
        ]);

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
            'job' => [
                'id' => $jobPosting->id,
                'title' => $jobPosting->title,
                'status' => $jobPosting->status,
                'company_id' => $jobPosting->company_id,
                'company_name' => $jobPosting->company?->name,
                'created_at' => $jobPosting->created_at?->toDateTimeString(),
            ],
            'applications' => $jobPosting->applications->map(fn ($app) => [
                'id' => $app->id,
                'candidate_name' => $app->candidate?->name,
                'candidate_email' => $app->candidate?->email,
                'stage' => $app->stage,
                'created_at' => $app->created_at?->toDateTimeString(),
            ]),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => self::STATUSES,
        ]);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:org_companies,id'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
        ]);

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
}

<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');

        $candidates = Candidate::query()
            ->withCount('applications')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->map(fn (Candidate $candidate) => [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'application_count' => $candidate->applications_count,
                'created_at' => $candidate->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Recruitment/Candidates/Index', [
            'candidates' => $candidates,
            'filters' => ['search' => $search],
            'summary' => ['total' => $candidates->count()],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $candidate = Candidate::query()->create($data);

        return redirect()
            ->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Candidate created.');
    }

    public function show(Candidate $candidate): Response
    {
        $candidate->load([
            'applications.jobPosting:id,title,status,company_id',
            'applications.jobPosting.company:id,name',
        ]);

        $duplicateEmail = $candidate->email
            ? Candidate::query()
                ->where('email', $candidate->email)
                ->where('id', '!=', $candidate->id)
                ->exists()
            : false;

        return Inertia::render('Recruitment/Candidates/Show', [
            'candidate' => [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'created_at' => $candidate->created_at?->toDateTimeString(),
            ],
            'duplicateEmail' => $duplicateEmail,
            'applications' => $candidate->applications->map(fn ($app) => [
                'id' => $app->id,
                'job_title' => $app->jobPosting?->title,
                'company_name' => $app->jobPosting?->company?->name,
                'stage' => $app->stage,
                'created_at' => $app->created_at?->toDateTimeString(),
            ]),
            'jobs' => JobPosting::query()
                ->whereIn('status', ['open', 'on_hold'])
                ->orderBy('title')
                ->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Candidate $candidate): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $candidate->update($data);

        return redirect()
            ->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Candidate updated.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        $candidate->delete();

        return redirect()
            ->route('recruitment.candidates.index')
            ->with('success', 'Candidate deleted.');
    }
}

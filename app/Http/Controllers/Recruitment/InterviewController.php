<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InterviewController extends Controller
{
    private const STATUSES = ['scheduled', 'completed', 'cancelled', 'no_show'];

    public function index(): Response
    {
        $interviews = Interview::query()
            ->with([
                'application:id,candidate_id,job_id,stage',
                'application.candidate:id,name',
                'application.jobPosting:id,title',
            ])
            ->orderByDesc('scheduled_at')
            ->get()
            ->map(fn (Interview $interview) => [
                'id' => $interview->id,
                'application_id' => $interview->application_id,
                'candidate_name' => $interview->application?->candidate?->name,
                'job_title' => $interview->application?->jobPosting?->title,
                'scheduled_at' => $interview->scheduled_at?->format('Y-m-d\TH:i'),
                'scheduled_at_label' => $interview->scheduled_at?->format('d M Y H:i'),
                'interviewer_name' => $interview->interviewer_name,
                'location' => $interview->location,
                'status' => $interview->status,
                'feedback' => $interview->feedback,
                'rating' => $interview->rating,
            ]);

        return Inertia::render('Recruitment/Interviews/Index', [
            'items' => $interviews,
            'applications' => $this->applicationOptions(),
            'statuses' => self::STATUSES,
            'summary' => [
                'total' => $interviews->count(),
                'scheduled' => $interviews->where('status', 'scheduled')->count(),
                'completed' => $interviews->where('status', 'completed')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Interview::query()->create($this->validated($request));

        return redirect()
            ->route('recruitment.interviews.index')
            ->with('success', 'Interview scheduled.');
    }

    public function update(Request $request, Interview $interview): RedirectResponse
    {
        $interview->update($this->validated($request));

        return redirect()
            ->route('recruitment.interviews.index')
            ->with('success', 'Interview updated.');
    }

    public function destroy(Interview $interview): RedirectResponse
    {
        $interview->delete();

        return redirect()
            ->route('recruitment.interviews.index')
            ->with('success', 'Interview deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'application_id' => ['required', 'exists:trx_applications,id'],
            'scheduled_at' => ['required', 'date'],
            'interviewer_name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
            'feedback' => ['nullable', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);
    }

    private function applicationOptions()
    {
        return Application::query()
            ->with(['candidate:id,name', 'jobPosting:id,title'])
            ->latest()
            ->get()
            ->map(fn (Application $app) => [
                'id' => $app->id,
                'name' => trim(($app->candidate?->name ?? 'Candidate').' — '.($app->jobPosting?->title ?? 'Job')),
            ]);
    }
}

<?php

namespace App\Http\Controllers\BugReport;

use App\Http\Controllers\Controller;
use App\Http\Requests\BugReport\StoreBugReportRequest;
use App\Http\Requests\BugReport\UpdateBugReportStatusRequest;
use App\Http\Resources\BugReportResource;
use App\Models\BugReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BugReportController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $query = BugReport::query()
            ->with('reporter:id,name')
            ->orderByDesc('created_at');

        if (is_string($status) && $status !== '' && in_array($status, BugReport::STATUSES, true)) {
            $query->where('status', $status);
        }

        return Inertia::render('BugReports/Index', [
            'reports' => $query->get()
                ->map(fn (BugReport $report) => (new BugReportResource($report))->resolve())
                ->values()
                ->all(),
            'filters' => [
                'status' => is_string($status) ? $status : '',
            ],
            'statuses' => BugReport::STATUSES,
        ]);
    }

    public function show(BugReport $bugReport): Response
    {
        $bugReport->load('reporter:id,name');

        return Inertia::render('BugReports/Show', [
            'report' => (new BugReportResource($bugReport))->resolve(),
            'statuses' => BugReport::STATUSES,
        ]);
    }

    public function store(StoreBugReportRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $consoleLog = [];

        if (! empty($data['console_log'])) {
            $decoded = json_decode($data['console_log'], true);
            $consoleLog = is_array($decoded) ? $decoded : [];
        }

        $path = $request->file('screenshot')->store('bug-reports', 'public');

        BugReport::query()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => 'todo',
            'url' => $data['url'],
            'page_title' => $data['page_title'] ?? null,
            'console_log' => $consoleLog,
            'user_agent' => $data['user_agent'] ?? null,
            'viewport_width' => $data['viewport_width'] ?? null,
            'viewport_height' => $data['viewport_height'] ?? null,
            'screenshot_path' => $path,
            'reported_by' => $request->user()?->id,
        ]);

        return redirect()->route('bug-reports.index')->with('success', 'Bug report submitted.');
    }

    public function updateStatus(UpdateBugReportStatusRequest $request, BugReport $bugReport): RedirectResponse
    {
        $bugReport->update([
            'status' => $request->validated('status'),
        ]);

        return redirect()->route('bug-reports.show', $bugReport)->with('success', 'Status updated.');
    }

    public function destroy(BugReport $bugReport): RedirectResponse
    {
        $bugReport->delete();

        return redirect()->route('bug-reports.index')->with('success', 'Bug report deleted.');
    }
}

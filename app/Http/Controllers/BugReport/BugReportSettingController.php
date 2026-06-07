<?php

namespace App\Http\Controllers\BugReport;

use App\Http\Controllers\Controller;
use App\Http\Requests\BugReport\UpdateBugReportSettingRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BugReportSettingController extends Controller
{
    public function edit(SettingsService $settings): Response
    {
        return Inertia::render('BugReports/Settings', [
            'enabled' => (bool) $settings->get('bug_report.enabled', true),
        ]);
    }

    public function update(UpdateBugReportSettingRequest $request, SettingsService $settings): RedirectResponse
    {
        $settings->set('bug_report.enabled', $request->boolean('enabled'));

        return redirect()->route('bug-reports.settings.edit')->with('success', 'Bug report settings saved.');
    }
}

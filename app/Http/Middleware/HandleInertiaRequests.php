<?php

namespace App\Http\Middleware;

use App\Services\ReminderSummaryService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'roles' => fn () => $request->user()
                    ? $request->user()->roleSlugs()->values()->all()
                    : [],
                'permissions' => fn () => $request->user()
                    ? $request->user()->permissionKeys()->values()->all()
                    : [],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
            'reminders' => fn () => $request->user()
                ? app(ReminderSummaryService::class)->summary()
                : null,
            'bugReport' => [
                'enabled' => fn () => $request->user()
                    ? (bool) app(SettingsService::class)->get('bug_report.enabled', true)
                    : false,
            ],
        ];
    }
}

<?php

namespace Tests\Feature\BugReport;

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BugReportSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('bug-reports.settings.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BugReports/Settings')
                ->where('enabled', true)
            );
    }

    public function test_update_toggle_persists_setting(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('bug-reports.settings.update'), [
                'enabled' => false,
            ])
            ->assertRedirect(route('bug-reports.settings.edit'));

        $service = app(SettingsService::class);
        $this->assertFalse($service->get('bug_report.enabled', true));
    }

    public function test_shared_prop_reflects_enabled_setting(): void
    {
        $user = User::factory()->create();
        app(SettingsService::class)->set('bug_report.enabled', false);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('bugReport.enabled', false)
            );
    }

    public function test_shared_prop_defaults_to_enabled(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('bugReport.enabled', true)
            );
    }
}

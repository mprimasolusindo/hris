<?php

namespace Tests\Unit;

use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_default_when_key_missing(): void
    {
        $service = app(SettingsService::class);

        $this->assertTrue($service->get('bug_report.enabled', true));
    }

    public function test_set_then_get_round_trips(): void
    {
        $service = app(SettingsService::class);

        $service->set('bug_report.enabled', false);

        $this->assertFalse($service->get('bug_report.enabled', true));
    }

    public function test_set_persists_to_database(): void
    {
        $service = app(SettingsService::class);

        $service->set('bug_report.enabled', '0');

        $this->assertDatabaseHas('sys_settings', [
            'key' => 'bug_report.enabled',
            'value' => '0',
        ]);
    }
}

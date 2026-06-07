<?php

namespace Tests\Feature\BugReport;

use App\Models\BugReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BugReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_bug_report_with_screenshot(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('bug-reports.store'), [
                'title' => 'Broken payroll button',
                'description' => 'Click does nothing',
                'url' => 'http://localhost/payroll',
                'page_title' => 'Payroll',
                'console_log' => json_encode([
                    ['level' => 'error', 'timestamp' => '2026-06-06T10:00:00Z', 'message' => 'TypeError: x is undefined'],
                ]),
                'user_agent' => 'Mozilla/5.0 Test',
                'viewport_width' => 1920,
                'viewport_height' => 1080,
                'screenshot' => UploadedFile::fake()->image('screenshot.png'),
            ]);

        $response->assertRedirect(route('bug-reports.index'));

        $this->assertDatabaseHas('sys_bug_reports', [
            'title' => 'Broken payroll button',
            'url' => 'http://localhost/payroll',
            'status' => 'todo',
            'reported_by' => $user->id,
        ]);

        $report = BugReport::query()->first();
        $this->assertNotNull($report);
        $this->assertNotNull($report->screenshot_path);
        Storage::disk('public')->assertExists($report->screenshot_path);
    }

    public function test_index_returns_inertia_with_reports(): void
    {
        $user = User::factory()->create();
        BugReport::query()->create([
            'title' => 'Test bug',
            'url' => 'http://localhost/dashboard',
            'status' => 'todo',
            'reported_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('bug-reports.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BugReports/Index')
                ->has('reports', 1)
                ->where('reports.0.title', 'Test bug')
            );
    }

    public function test_show_renders_bug_report_detail(): void
    {
        $user = User::factory()->create();
        $report = BugReport::query()->create([
            'title' => 'Detail bug',
            'description' => 'Some detail',
            'url' => 'http://localhost/employees',
            'status' => 'in_progress',
            'reported_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('bug-reports.show', $report))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BugReports/Show')
                ->where('report.title', 'Detail bug')
                ->where('report.status', 'in_progress')
            );
    }

    public function test_update_status_changes_status(): void
    {
        $user = User::factory()->create();
        $report = BugReport::query()->create([
            'title' => 'Status bug',
            'url' => 'http://localhost/dashboard',
            'status' => 'todo',
            'reported_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->patch(route('bug-reports.status.update', $report), [
                'status' => 'done',
            ])
            ->assertRedirect(route('bug-reports.show', $report));

        $this->assertSame('done', $report->fresh()->status);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $user = User::factory()->create();
        $report = BugReport::query()->create([
            'title' => 'Invalid status',
            'url' => 'http://localhost/dashboard',
            'status' => 'todo',
            'reported_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->patch(route('bug-reports.status.update', $report), [
                'status' => 'invalid_status',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_destroy_soft_deletes_report(): void
    {
        $user = User::factory()->create();
        $report = BugReport::query()->create([
            'title' => 'Delete me',
            'url' => 'http://localhost/dashboard',
            'status' => 'todo',
            'reported_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('bug-reports.destroy', $report))
            ->assertRedirect(route('bug-reports.index'));

        $this->assertSoftDeleted('sys_bug_reports', ['id' => $report->id]);
    }
}

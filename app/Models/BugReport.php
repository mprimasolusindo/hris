<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Bug report submitted via the global widget.
 *
 * status values: todo | in_progress | failed | ready_for_review | on_review | closed | done
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $url
 * @property string|null $page_title
 * @property array<int, array<string, mixed>>|null $console_log
 * @property string|null $user_agent
 * @property int|null $viewport_width
 * @property int|null $viewport_height
 * @property string|null $screenshot_path
 * @property int|null $reported_by
 */
class BugReport extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'todo',
        'in_progress',
        'failed',
        'ready_for_review',
        'on_review',
        'closed',
        'done',
    ];

    protected $table = 'sys_bug_reports';

    protected $fillable = [
        'title',
        'description',
        'status',
        'url',
        'page_title',
        'console_log',
        'user_agent',
        'viewport_width',
        'viewport_height',
        'screenshot_path',
        'reported_by',
    ];

    protected function casts(): array
    {
        return [
            'console_log' => 'array',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}

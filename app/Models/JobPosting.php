<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * JobPosting — recruitment vacancy. Stored in `trx_jobs` (distinct from
 * Laravel's framework-level `jobs` queue table). Class is named JobPosting
 * to avoid confusion with Illuminate\Queue\Job.
 *
 * status values: draft | open | on_hold | closed | filled | cancelled
 * employment_type: permanent | contract | outsourced | internship
 * priority: low | medium | high
 *
 * @property int $id
 * @property int $company_id
 * @property int|null $site_id
 * @property int|null $department_id
 * @property int|null $position_id
 * @property string $title
 * @property string|null $code
 * @property string $employment_type
 * @property int $headcount
 * @property int $filled_count
 * @property string $status
 * @property string $priority
 * @property \Illuminate\Support\Carbon|null $opened_at
 * @property \Illuminate\Support\Carbon|null $target_close_date
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property int|null $hiring_manager_id
 * @property int|null $recruiter_id
 * @property string|null $salary_min
 * @property string|null $salary_max
 * @property string $currency
 * @property string|null $description
 * @property string|null $requirements
 * @property string|null $benefits
 * @property string|null $location_note
 */
class JobPosting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_jobs';

    protected $fillable = [
        'company_id',
        'site_id',
        'department_id',
        'position_id',
        'title',
        'code',
        'employment_type',
        'headcount',
        'filled_count',
        'status',
        'priority',
        'opened_at',
        'target_close_date',
        'closed_at',
        'hiring_manager_id',
        'recruiter_id',
        'salary_min',
        'salary_max',
        'currency',
        'description',
        'requirements',
        'benefits',
        'location_note',
    ];

    protected function casts(): array
    {
        return [
            'headcount' => 'integer',
            'filled_count' => 'integer',
            'opened_at' => 'date',
            'target_close_date' => 'date',
            'closed_at' => 'datetime',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function hiringManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hiring_manager_id');
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'recruiter_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id');
    }
}

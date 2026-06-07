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
 * status values: open | closed | on_hold | filled
 *
 * @property int $id
 * @property int $company_id
 * @property string $title
 * @property string $status
 */
class JobPosting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_jobs';

    protected $fillable = ['company_id', 'title', 'status'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id');
    }
}

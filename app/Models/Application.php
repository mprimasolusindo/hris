<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Application — pipeline link between a Candidate and a JobPosting.
 *
 * stage values (typical pipeline): applied | screening | interview | offer | hired | rejected
 *
 * @property int $id
 * @property int $candidate_id
 * @property int $job_id
 * @property string $stage
 */
class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_applications';

    protected $fillable = ['candidate_id', 'job_id', 'stage'];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }
}

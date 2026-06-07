<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PerformanceReview — periodic (quarterly) performance review for an employee.
 *
 * status values: draft | submitted | acknowledged | finalized
 *
 * @property int $id
 * @property int $employee_id
 * @property int|null $reviewer_id
 * @property int $period_year
 * @property int $period_quarter
 * @property string $rating   decimal(3,2)
 * @property string|null $goals
 * @property string|null $notes
 * @property string $status
 */
class PerformanceReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tal_performance_reviews';

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'period_year',
        'period_quarter',
        'rating',
        'goals',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_quarter' => 'integer',
            'rating' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}

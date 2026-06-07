<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NineBoxAssessment — places an employee on the 9-box grid for a period using
 * performance and potential scores (1-3 each).
 *
 * @property int $id
 * @property int $employee_id
 * @property int $period_year
 * @property int $performance_score
 * @property int $potential_score
 * @property string|null $box_label
 * @property string|null $notes
 */
class NineBoxAssessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tal_nine_box_assessments';

    protected $fillable = [
        'employee_id',
        'period_year',
        'performance_score',
        'potential_score',
        'box_label',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'performance_score' => 'integer',
            'potential_score' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

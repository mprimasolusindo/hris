<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SuccessionPlan — names a successor (and optional incumbent) for a position
 * with a readiness assessment.
 *
 * readiness values: ready_now | ready_1_2_years | ready_3_plus_years
 *
 * @property int $id
 * @property int $position_id
 * @property int $successor_id
 * @property int|null $incumbent_id
 * @property string $readiness
 * @property string|null $notes
 */
class SuccessionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tal_succession_plans';

    protected $fillable = ['position_id', 'successor_id', 'incumbent_id', 'readiness', 'notes'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function successor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'successor_id');
    }

    public function incumbent(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'incumbent_id');
    }
}

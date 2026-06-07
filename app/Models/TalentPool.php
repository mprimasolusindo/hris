<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TalentPool — flags an employee as part of a talent pool with readiness and
 * potential ratings.
 *
 * readiness values: ready_now | ready_1_2_years | ready_3_plus_years
 * potential values: low | medium | high
 *
 * @property int $id
 * @property int $employee_id
 * @property string $readiness
 * @property string $potential
 * @property string|null $notes
 */
class TalentPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tal_talent_pool';

    protected $fillable = ['employee_id', 'readiness', 'potential', 'notes'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

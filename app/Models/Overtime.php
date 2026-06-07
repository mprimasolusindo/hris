<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Overtime — lembur claim. Hourly rate per PP 35/2021 ps. 30-32:
 *   1/173 × monthly wage per hour (statutory baseline).
 *
 * status values: pending | approved | rejected
 *
 * @property int $id
 * @property int $employee_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $hours       decimal(6,2)
 * @property int|null $approved_by
 * @property string $status
 */
class Overtime extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ot_overtimes';

    protected $fillable = ['employee_id', 'date', 'hours', 'approved_by', 'status'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'hours' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}

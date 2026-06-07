<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EmployeeShift — pivot assigning a shift to an employee on a given date.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $shift_id
 * @property \Illuminate\Support\Carbon $date
 */
class EmployeeShift extends Model
{
    use HasFactory;

    protected $table = 'rel_employee_shifts';

    protected $fillable = ['employee_id', 'shift_id', 'date'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}

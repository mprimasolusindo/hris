<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Shift — work-shift template (start/end time of day).
 *
 * @property int $id
 * @property string $name
 * @property string $start_time   H:i:s
 * @property string $end_time     H:i:s
 */
class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'att_shifts';

    protected $fillable = ['name', 'start_time', 'end_time'];

    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }
}

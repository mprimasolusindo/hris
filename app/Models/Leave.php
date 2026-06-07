<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Leave — cuti / izin / sakit request.
 *
 * type values:   annual | sick | unpaid | maternity | paternity | marriage | bereavement | other
 * status values: pending | approved | rejected | cancelled
 *
 * @property int $id
 * @property int $employee_id
 * @property string $type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property string $status
 */
class Leave extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lv_leaves';

    protected $fillable = ['employee_id', 'type', 'start_date', 'end_date', 'status'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

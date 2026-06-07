<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attendance — single clock-in/clock-out event with optional GPS.
 *
 * status values: present | late | absent | leave | sick | holiday
 *
 * Append-only: no soft delete. Once posted to a payroll period, treat as immutable.
 *
 * @property int $id
 * @property int $employee_id
 * @property int|null $site_id
 * @property \Illuminate\Support\Carbon|null $clock_in
 * @property \Illuminate\Support\Carbon|null $clock_out
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string $status
 */
class Attendance extends Model
{
    use HasFactory;

    protected $table = 'att_attendances';

    protected $fillable = [
        'employee_id', 'site_id', 'clock_in', 'clock_out',
        'latitude', 'longitude', 'status',
    ];

    protected function casts(): array
    {
        return [
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}

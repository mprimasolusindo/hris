<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

/**
 * Work schedule — weekly work/day-off pattern for fixed-hour staff.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $is_default
 * @property string|null $default_start_time H:i:s
 * @property string|null $default_end_time H:i:s
 * @property bool $mon_working
 * @property bool $tue_working
 * @property bool $wed_working
 * @property bool $thu_working
 * @property bool $fri_working
 * @property bool $sat_working
 * @property bool $sun_working
 */
class WorkSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'att_work_schedules';

    protected $fillable = [
        'name',
        'code',
        'is_default',
        'default_start_time',
        'default_end_time',
        'mon_working',
        'tue_working',
        'wed_working',
        'thu_working',
        'fri_working',
        'sat_working',
        'sun_working',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'mon_working' => 'boolean',
            'tue_working' => 'boolean',
            'wed_working' => 'boolean',
            'thu_working' => 'boolean',
            'fri_working' => 'boolean',
            'sat_working' => 'boolean',
            'sun_working' => 'boolean',
        ];
    }

    /**
     * Whether the ISO-8601 weekday is a working day under this schedule.
     *
     * @param  int  $isoDow  1 = Monday … 7 = Sunday
     */
    public function isWorkingWeekday(int $isoDow): bool
    {
        return match ($isoDow) {
            1 => (bool) $this->mon_working,
            2 => (bool) $this->tue_working,
            3 => (bool) $this->wed_working,
            4 => (bool) $this->thu_working,
            5 => (bool) $this->fri_working,
            6 => (bool) $this->sat_working,
            7 => (bool) $this->sun_working,
            default => throw new InvalidArgumentException(
                "isoDow must be 1–7, got {$isoDow}"
            ),
        };
    }
}

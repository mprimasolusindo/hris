<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Calendar holiday / non-working date.
 *
 * type values: national | company | joint_leave
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $holiday_date
 * @property string $type
 * @property bool $is_half_day
 * @property string|null $notes
 */
class Holiday extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'att_holidays';

    protected $fillable = [
        'name',
        'holiday_date',
        'type',
        'is_half_day',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_half_day' => 'boolean',
        ];
    }
}

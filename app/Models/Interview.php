<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Interview — a scheduled interview tied to a recruitment Application.
 *
 * status values: scheduled | completed | cancelled | no_show
 *
 * @property int $id
 * @property int $application_id
 * @property \Illuminate\Support\Carbon $scheduled_at
 * @property string $interviewer_name
 * @property string|null $location
 * @property string $status
 * @property string|null $feedback
 * @property int|null $rating
 */
class Interview extends Model
{
    use HasFactory;

    protected $table = 'trx_interviews';

    protected $fillable = [
        'application_id',
        'scheduled_at',
        'interviewer_name',
        'location',
        'status',
        'feedback',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}

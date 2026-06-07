<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Training — a training program / session employees can enroll in.
 *
 * status values: planned | ongoing | completed | cancelled
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property string|null $location
 * @property string $status
 */
class Training extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tal_trainings';

    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'location', 'status'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'rel_training_employees', 'training_id', 'employee_id')
            ->withPivot('status')
            ->withTimestamps();
    }
}

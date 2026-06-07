<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TrainingEmployee — pivot enrolling an employee in a training program.
 *
 * status values: registered | attended | completed | dropped
 *
 * @property int $id
 * @property int $training_id
 * @property int $employee_id
 * @property string $status
 */
class TrainingEmployee extends Model
{
    use HasFactory;

    protected $table = 'rel_training_employees';

    protected $fillable = ['training_id', 'employee_id', 'status'];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $employee_id
 * @property string $name
 * @property string|null $relationship
 * @property string $phone
 */
class EmployeeEmergencyContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_emergency_contacts';

    protected $fillable = ['employee_id', 'name', 'relationship', 'phone'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

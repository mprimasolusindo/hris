<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeFamilyMember — for PTKP / dependent calculations and HR records.
 *
 * relationship values: spouse | child | parent | sibling | other
 *
 * @property int $id
 * @property int $employee_id
 * @property string $name
 * @property string $relationship
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property bool $is_dependent
 */
class EmployeeFamilyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_family_members';

    protected $fillable = ['employee_id', 'name', 'relationship', 'birth_date', 'is_dependent'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_dependent' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

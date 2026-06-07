<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeJob — employment-history row linking an Employee to a Company,
 * Department, Position, optional manager, and an effective date range.
 *
 * employment_type values (mirrors emp_contracts.contract_type for convenience):
 *   pkwt | pkwtt | outsourcing | magang
 *
 * @property int $id
 * @property int $employee_id
 * @property int $company_id
 * @property int|null $department_id
 * @property int|null $position_id
 * @property int|null $manager_id
 * @property string|null $employment_type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 */
class EmployeeJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_jobs';

    protected $fillable = [
        'employee_id', 'company_id', 'department_id', 'position_id',
        'manager_id', 'employment_type', 'start_date', 'end_date',
    ];

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}

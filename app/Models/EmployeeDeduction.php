<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeDeduction — recurring potongan assigned from cfg_salary_components catalog.
 *
 * status values: active | inactive
 *
 * @property int $id
 * @property int $employee_id
 * @property int|null $component_id
 * @property string $name
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $effective_start
 * @property \Illuminate\Support\Carbon|null $effective_end
 * @property string $status
 * @property bool $recurring
 */
class EmployeeDeduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_deductions';

    protected $fillable = [
        'employee_id',
        'component_id',
        'name',
        'value',
        'effective_start',
        'effective_end',
        'status',
        'recurring',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'effective_start' => 'date',
            'effective_end' => 'date',
            'recurring' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }
}

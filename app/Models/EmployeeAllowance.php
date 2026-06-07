<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeAllowance — fixed recurring tunjangan attached to an employee
 * (transport, makan, jabatan, komunikasi, kehadiran, etc.).
 *
 * status values: active | inactive
 *
 * @property int $id
 * @property int $employee_id
 * @property int|null $component_id
 * @property string $name
 * @property string $amount   decimal(18,2)
 * @property bool $taxable
 * @property \Illuminate\Support\Carbon|null $effective_start
 * @property \Illuminate\Support\Carbon|null $effective_end
 * @property string $status
 * @property bool $recurring
 */
class EmployeeAllowance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_allowances';

    protected $fillable = [
        'employee_id',
        'component_id',
        'name',
        'amount',
        'taxable',
        'effective_start',
        'effective_end',
        'status',
        'recurring',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'taxable' => 'boolean',
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

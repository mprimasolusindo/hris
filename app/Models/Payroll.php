<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Payroll — single employee's payroll for a (year, month) period.
 *
 * Append-only: no soft delete. Once posted, treat as immutable; corrections
 * happen via a new offsetting period or an explicit adjustment row.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $period_month
 * @property int $period_year
 * @property string $gross_salary       decimal(18,2)
 * @property string $total_deduction    decimal(18,2)
 * @property string $net_salary         decimal(18,2)
 * @property string $status
 */
class Payroll extends Model
{
    use HasFactory;

    protected $table = 'pay_payrolls';

    protected $fillable = [
        'employee_id', 'period_month', 'period_year',
        'gross_salary', 'total_deduction', 'net_salary', 'status',
        'approval_notes', 'reviewed_by', 'reviewed_at',
        'approved_by', 'approved_at', 'paid_by', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'gross_salary' => 'decimal:2',
            'total_deduction' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}

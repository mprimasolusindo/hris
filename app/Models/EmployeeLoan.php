<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeLoan — pinjaman karyawan, deducted monthly via payroll.
 *
 * @property int $id
 * @property int $employee_id
 * @property string $amount             decimal(18,2)
 * @property string $remaining_amount   decimal(18,2)
 * @property string $monthly_deduction  decimal(18,2)
 */
class EmployeeLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_loans';

    protected $fillable = ['employee_id', 'amount', 'remaining_amount', 'monthly_deduction'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'monthly_deduction' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

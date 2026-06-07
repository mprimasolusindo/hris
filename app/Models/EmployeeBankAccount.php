<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeBankAccount — payroll disbursement target account(s).
 *
 * @property int $id
 * @property int $employee_id
 * @property string $bank_name
 * @property string $account_number
 * @property string $account_holder
 * @property bool $is_primary
 */
class EmployeeBankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_bank_accounts';

    protected $fillable = ['employee_id', 'bank_name', 'account_number', 'account_holder', 'is_primary'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

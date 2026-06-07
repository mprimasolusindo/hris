<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PayrollItem — single earning or deduction line on a payroll.
 *
 * type values: earning | deduction
 * Append-only: no soft delete.
 *
 * @property int $id
 * @property int $payroll_id
 * @property string $component_name
 * @property string $type
 * @property string $amount        decimal(18,2)
 */
class PayrollItem extends Model
{
    use HasFactory;

    protected $table = 'pay_payroll_items';

    protected $fillable = ['payroll_id', 'component_name', 'type', 'amount'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}

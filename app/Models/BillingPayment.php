<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BillingPayment — append-only ledger of subscription payments.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $amount        decimal(18,2) IDR
 * @property string|null $method
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_at
 */
class BillingPayment extends Model
{
    use HasFactory;

    protected $table = 'bill_payments';

    protected $fillable = ['tenant_id', 'amount', 'method', 'status', 'paid_at'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

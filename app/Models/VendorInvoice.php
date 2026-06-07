<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VendorInvoice — append-only billing record for an outsourcing vendor service
 * period. Transactional ledger; no soft delete.
 *
 * status values: draft | issued | paid | overdue | cancelled
 *
 * @property int $id
 * @property int $vendor_id
 * @property int|null $tenant_id
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon $period_start
 * @property \Illuminate\Support\Carbon $period_end
 * @property string $amount   decimal(18,2) IDR
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_at
 */
class VendorInvoice extends Model
{
    use HasFactory;

    protected $table = 'bill_vendor_invoices';

    protected $fillable = [
        'vendor_id',
        'tenant_id',
        'invoice_number',
        'period_start',
        'period_end',
        'amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'vendor_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

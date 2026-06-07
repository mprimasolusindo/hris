<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * OutsourcingComplianceRecord — a compliance flag raised against a
 * vendor-supplied (alih daya) employee, with resolution workflow.
 *
 * flag_type values: expired_contract | missing_bpjs | wage_below_minimum | document_missing | other
 * status values: open | in_review | resolved | dismissed
 *
 * @property int $id
 * @property int $employee_id
 * @property int $vendor_id
 * @property string $flag_type
 * @property string $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property int|null $resolved_by
 */
class OutsourcingComplianceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'outsourcing_compliance_records';

    protected $fillable = [
        'employee_id',
        'vendor_id',
        'flag_type',
        'description',
        'status',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'vendor_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}

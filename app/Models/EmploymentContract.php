<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmploymentContract — written agreement between employee and company.
 *
 * contract_type values:
 *   pkwt        : fixed-term (PP 35/2021 ps. 8 — max 5 yrs incl. extension; no probation)
 *   pkwtt       : permanent (probation up to 3 months allowed per UU 13/2003 ps. 60)
 *   outsourcing : alih daya (PP 35/2021 ps. 18-19)
 *   magang      : intern / apprenticeship (Permenaker 6/2020)
 *
 * @property int $id
 * @property int $employee_id
 * @property string $contract_type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string $salary_base   decimal(18,2) IDR
 */
class EmploymentContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_contracts';

    protected $fillable = ['employee_id', 'contract_type', 'start_date', 'end_date', 'salary_base'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'salary_base' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

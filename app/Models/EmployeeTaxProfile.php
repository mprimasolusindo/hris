<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeTaxProfile — PPh21 / PTKP status for an employee.
 *
 * tax_status values (PMK 168/2023 / UU 7/2021 HPP):
 *   TK/0, TK/1, TK/2, TK/3, K/0, K/1, K/2, K/3
 * TER category mapping (PMK 168/2023):
 *   A = TK/0, TK/1, K/0
 *   B = TK/2, TK/3, K/1, K/2
 *   C = K/3
 *
 * tax_method values: ter_monthly | annual_adjustment
 *
 * @property int $id
 * @property int $employee_id
 * @property bool $has_npwp
 * @property string|null $npwp
 * @property string|null $tax_status
 * @property string $tax_method
 * @property int $dependents_count
 */
class EmployeeTaxProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_tax_profiles';

    protected $fillable = [
        'employee_id',
        'has_npwp',
        'npwp',
        'tax_status',
        'tax_method',
        'dependents_count',
    ];

    protected function casts(): array
    {
        return [
            'has_npwp' => 'boolean',
            'dependents_count' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

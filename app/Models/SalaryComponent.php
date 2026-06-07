<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SalaryComponent — catalog of payroll line-item names (gaji pokok, tunjangan
 * transport, BPJS Kesehatan, PPh21, etc.).
 *
 * type values: earning | deduction
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * calculation_method values: fixed | percentage | formula
 *
 * @property string|null $code
 * @property string $calculation_method
 * @property string $default_value
 * @property bool $is_taxable
 */
class SalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cfg_salary_components';

    protected $fillable = [
        'name',
        'code',
        'type',
        'calculation_method',
        'default_value',
        'is_taxable',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'default_value' => 'decimal:2',
        ];
    }
}

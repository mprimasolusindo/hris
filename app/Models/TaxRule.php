<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TaxRule — generic key/value store for PPh21 PTKP thresholds, TER rates,
 * Pasal 17 brackets, etc. Source values via the HR research skill / sub-agent
 * against PMK 168/2023 and UU 7/2021 (HPP); never hard-code.
 *
 * @property int $id
 * @property string $name
 * @property string $value   decimal(18,4)
 * @property string|null $rule_type
 * @property string|null $ptkp_category
 * @property string|null $gross_min   decimal(18,2)
 * @property string|null $gross_max   decimal(18,2)
 */
class TaxRule extends Model
{
    use HasFactory;

    protected $table = 'cfg_tax_rules';

    protected $fillable = ['name', 'value', 'rule_type', 'ptkp_category', 'gross_min', 'gross_max'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'gross_min' => 'decimal:2',
            'gross_max' => 'decimal:2',
        ];
    }
}

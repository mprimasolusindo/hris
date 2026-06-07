<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * BpjsConfig — iuran percentages per BPJS program. Source values via the
 * HR research skill / sub-agent against the current regulation; never
 * hard-code in calculator code.
 *
 * type values:
 *   kesehatan : BPJS Kesehatan        (Perpres 64/2020)
 *   jht       : Jaminan Hari Tua      (PP 46/2015)
 *   jp        : Jaminan Pensiun       (PP 45/2015)
 *   jkk       : Jaminan Kecelakaan Kerja (PP 44/2015 — risk-tier based)
 *   jkm       : Jaminan Kematian      (PP 44/2015)
 *   jkp       : Jaminan Kehilangan Pekerjaan (PP 37/2021)
 *
 * Percentages stored as decimal(7,4): 0.0570 = 5.70%.
 *
 * @property int $id
 * @property string $type
 * @property string $employee_percentage
 * @property string $company_percentage
 */
class BpjsConfig extends Model
{
    use HasFactory;

    protected $table = 'cfg_bpjs';

    protected $fillable = ['type', 'employee_percentage', 'company_percentage'];

    protected function casts(): array
    {
        return [
            'employee_percentage' => 'decimal:4',
            'company_percentage' => 'decimal:4',
        ];
    }
}

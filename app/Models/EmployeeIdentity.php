<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EmployeeIdentity — government IDs for an employee.
 *
 * nik             : NIK (Nomor Induk Kependudukan)
 * npwp            : NPWP (Nomor Pokok Wajib Pajak); since UU 7/2021 (HPP) NIK can act as NPWP.
 * bpjs_health     : BPJS Kesehatan number
 * bpjs_employment : BPJS Ketenagakerjaan number
 *
 * @property int $id
 * @property int $employee_id
 * @property string|null $nik
 * @property string|null $npwp
 * @property string|null $bpjs_health
 * @property string|null $bpjs_employment
 * @property string|null $address
 * @property string|null $city
 */
class EmployeeIdentity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emp_identities';

    protected $fillable = [
        'employee_id', 'nik', 'npwp', 'bpjs_health', 'bpjs_employment',
        'address', 'city',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

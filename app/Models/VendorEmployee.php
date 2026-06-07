<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VendorEmployee — outsourcing link: an employee is contracted by a vendor
 * (a Company with type='vendor') and deployed at the user company.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $vendor_id
 */
class VendorEmployee extends Model
{
    use HasFactory;

    protected $table = 'rel_vendor_employees';

    protected $fillable = ['employee_id', 'vendor_id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'vendor_id');
    }
}

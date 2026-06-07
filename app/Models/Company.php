<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Company — main employer or vendor entity. Tenant-scoped.
 * type values: main | vendor
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $type
 */
class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'org_companies';

    protected $fillable = ['tenant_id', 'name', 'type'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function employeeJobs(): HasMany
    {
        return $this->hasMany(EmployeeJob::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    /** Outsourced workers when this company plays the vendor role. */
    public function vendorEmployees(): HasMany
    {
        return $this->hasMany(VendorEmployee::class, 'vendor_id');
    }
}

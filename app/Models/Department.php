<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Department — internal org unit within a company.
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 */
class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'org_departments';

    protected $fillable = ['company_id', 'name'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeJobs(): HasMany
    {
        return $this->hasMany(EmployeeJob::class);
    }
}

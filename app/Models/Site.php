<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Site — a physical work location (branch / project site / outsourcing site).
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $location
 */
class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'org_sites';

    protected $fillable = ['company_id', 'name', 'location'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeSites(): HasMany
    {
        return $this->hasMany(EmployeeSite::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Position — job title catalog. Per the discovery schema this is shared
 * across companies; revisit if it should be tenant-scoped.
 *
 * @property int $id
 * @property string $name
 */
class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'org_positions';

    protected $fillable = ['name'];

    public function employeeJobs(): HasMany
    {
        return $this->hasMany(EmployeeJob::class);
    }
}

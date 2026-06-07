<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Candidate — applicant (pre-hire). On hire, an Employee record is created.
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 */
class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_candidates';

    protected $fillable = ['name', 'email', 'phone'];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}

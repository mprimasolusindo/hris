<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SubscriptionPlan — catalog of plans tenants can subscribe to.
 *
 * @property int $id
 * @property string $name
 * @property string $price        decimal(18,2) IDR
 * @property int|null $employee_limit
 */
class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sub_plans';

    protected $fillable = ['name', 'price', 'employee_limit'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'employee_limit' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}

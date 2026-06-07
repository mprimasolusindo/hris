<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant — top-level SaaS tenant. Every tenant-scoped row chains back here.
 *
 * @property int $id
 * @property string $name
 * @property string $status
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sys_tenants';

    protected $fillable = ['name', 'status'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function billingPayments(): HasMany
    {
        return $this->hasMany(BillingPayment::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}

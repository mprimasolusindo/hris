<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * LeaveType — master catalog of leave categories with default annual
 * entitlement and paid/unpaid flag.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $annual_entitlement_days
 * @property bool $is_paid
 */
class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lv_leave_types';

    protected $fillable = ['code', 'name', 'annual_entitlement_days', 'is_paid'];

    protected function casts(): array
    {
        return [
            'annual_entitlement_days' => 'integer',
            'is_paid' => 'boolean',
        ];
    }
}

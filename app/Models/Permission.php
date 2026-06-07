<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $module
 */
class Permission extends Model
{
    protected $table = 'sys_permissions';

    protected $fillable = [
        'key',
        'name',
        'module',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'rel_role_permissions', 'permission_id', 'role_id')
            ->withTimestamps();
    }
}

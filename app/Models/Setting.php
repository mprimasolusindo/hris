<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    protected $table = 'sys_settings';

    protected $fillable = ['key', 'value'];
}

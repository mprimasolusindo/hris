<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::all() as $permission) {
            Permission::query()->updateOrCreate(
                ['key' => $permission['key']],
                [
                    'name' => $permission['name'],
                    'module' => $permission['module'],
                ]
            );
        }
    }
}

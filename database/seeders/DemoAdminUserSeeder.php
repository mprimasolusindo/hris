<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates a fixed admin account for local / demo login (Laravel Breeze).
 *
 * Credentials (change in production):
 * - Email:    admin@demo.hris.local
 * - Password: password
 */
class DemoAdminUserSeeder extends Seeder
{
    public const DEMO_EMAIL = 'admin@demo.hris.local';

    public const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Demo Admin HRIS',
                'password' => Hash::make(self::DEMO_PASSWORD),
                'email_verified_at' => now(),
            ]
        );

        $superAdmin = Role::query()->where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $user->roles()->syncWithoutDetaching([$superAdmin->id]);
        }

        $this->command?->info('Demo login: '.self::DEMO_EMAIL.' / '.self::DEMO_PASSWORD);
    }
}

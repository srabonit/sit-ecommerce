<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // .env theke values collect kora (default fallback shoho)
        $adminName     = env('ADMIN_NAME', env('INITIAL_ADMIN_NAME', 'Super Admin'));
        $adminEmail    = env('ADMIN_EMAIL', env('INITIAL_ADMIN_EMAIL', 'admin@example.com'));
        $adminPhone    = env('ADMIN_PHONE', env('INITIAL_ADMIN_PHONE', '01700000000'));
        $adminPassword = env('ADMIN_PASSWORD', env('INITIAL_ADMIN_PASSWORD', 'password123'));

        $adminData = [
            'name'       => $adminName,
            'phone'      => $adminPhone,
            'password'   => Hash::make($adminPassword),
            'role'       => 'admin',
            'is_active'  => 1,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'user_type')) {
            $adminData['user_type'] = 'admin';
        }

        // Jodi email diye user already thake tahole update hobe, na thakle create hobe
        User::withTrashed()->updateOrInsert(
            ['email' => $adminEmail],
            array_merge($adminData, [
                'created_at' => now(),
                'deleted_at' => null, // trash e thakle restore korbe
            ])
        );

        $this->command->info("Initial Admin successfully created or updated with email: {$adminEmail}");
    }
}
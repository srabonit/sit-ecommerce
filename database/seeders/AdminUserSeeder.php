<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'info.luckysignature@gmail.com');
        $adminName = env('ADMIN_NAME', 'Lucky Signature');
        $adminPassword = env('ADMIN_PASSWORD', 'Captin@20212450');

        $exists = DB::table('users')->where('email', $adminEmail)->first();

        if (!$exists) {
            DB::table('users')->insert([
                'name'       => $adminName,
                'email'      => $adminEmail,
                'password'   => Hash::make($adminPassword),
                'role'       => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('email', $adminEmail)->update([
                'role'       => 'admin',
                'password'   => Hash::make($adminPassword),
                'updated_at' => now(),
            ]);
        }
    }
}
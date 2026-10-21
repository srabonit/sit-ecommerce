<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_title', 'value' => 'ECOM SITE'],
            ['key' => 'site_tagline', 'value' => 'BEST ONLINE SHOP IN BD'],
            ['key' => 'tagline_status', 'value' => 'on'],
            ['key' => 'logo_title', 'value' => 'ECOM SITE'],
            ['key' => 'header_logo', 'value' => 'https://www.srabonit.com/favicon.jpg'],
            ['key' => 'logo_width', 'value' => '150'],
            ['key' => 'logo_height', 'value' => '40'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'updated_at' => now()]
            );
        }
    }
}
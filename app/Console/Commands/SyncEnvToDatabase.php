<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SyncEnvToDatabase extends Command
{
    protected $signature = 'env:sync-db';
    protected $description = 'Sync all .env file variables to database settings table and replace existing content';

    public function handle()
    {
        // Check if settings table exists
        if (!Schema::hasTable('settings')) {
            $this->error("Database-e 'settings' table pawa jayni! Prothome migration run korun.");
            return;
        }

        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->error(".env file pawa jayni!");
            return;
        }

        $envContent = File::get($envPath);
        $lines = explode("\n", $envContent);
        $syncedCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                list($key, $value) = explode('=', $line, 2);
                
                $key = trim($key);
                $value = trim($value, "\" '"); // Remove extra quotes

                if (!empty($key)) {
                    // Update or insert to overwrite existing keys
                    DB::table('settings')->updateOrInsert(
                        ['key' => $key],
                        [
                            'value' => $value,
                            'updated_at' => now(),
                            'created_at' => now()
                        ]
                    );

                    $syncedCount++;
                }
            }
        }

        $this->info("Successfully synced {$syncedCount} variables from .env to database (existing keys replaced)!");
    }
}
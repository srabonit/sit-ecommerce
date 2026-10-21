<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\RbacDatabaseSeeder;

class RbacSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rbac:sync';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize system module permissions and configure root RBAC matrices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting RBAC permissions synchronization...');

        if (Schema::hasTable('role_route_permissions')) {
            $cols = Schema::getColumnListing('role_route_permissions');
            $this->comment('Detected role_route_permissions columns: [' . implode(', ', $cols) . ']');
        }

        try {
            $seeder = new RbacDatabaseSeeder();
            $seeder->run();

            $this->newLine();
            $this->info('====================================================');
            $this->info(' ✓ All module routes & permissions synchronized.');
            $this->info(' ✓ Dynamic route_module & role mappings resolved.');
            $this->info(' ✓ Admin & Branch Manager permissions updated.');
            $this->info(' ✓ Root Super Admin account verified & protected.');
            $this->info(' ✓ System permission caches cleared successfully.');
            $this->info('====================================================');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('RBAC Sync Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
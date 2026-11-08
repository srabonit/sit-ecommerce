<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure sales_person_id & branch_id in orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'sales_person_id')) {
                    $table->unsignedBigInteger('sales_person_id')->nullable();
                    $table->index('sales_person_id');
                }
                if (!Schema::hasColumn('orders', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable();
                    $table->index('branch_id');
                }
            });
        }

        // 2. Add branch_id to inventory_movements table for branch-level stock ledger
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_movements', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable();
                    $table->index('branch_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'sales_person_id')) {
                    $table->dropIndex(['sales_person_id']);
                    $table->dropColumn('sales_person_id');
                }
            });
        }

        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_movements', 'branch_id')) {
                    $table->dropIndex(['branch_id']);
                    $table->dropColumn('branch_id');
                }
            });
        }
    }
};
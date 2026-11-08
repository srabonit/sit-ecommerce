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
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (!Schema::hasColumn('branches', 'branch_code')) {
                    $table->string('branch_code', 50)->nullable();
                }
                if (!Schema::hasColumn('branches', 'branch_type')) {
                    $table->string('branch_type', 40)->default('retail');
                }
                if (!Schema::hasColumn('branches', 'manager_id')) {
                    $table->unsignedBigInteger('manager_id')->nullable();
                }
                if (!Schema::hasColumn('branches', 'status')) {
                    $table->string('status', 30)->default('active');
                }
                if (!Schema::hasColumn('branches', 'opening_date')) {
                    $table->date('opening_date')->nullable();
                }
                if (!Schema::hasColumn('branches', 'opening_hours')) {
                    $table->string('opening_hours', 191)->nullable();
                }
                if (!Schema::hasColumn('branches', 'branch_logo')) {
                    $table->string('branch_logo', 255)->nullable();
                }
                if (!Schema::hasColumn('branches', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('branches', 'cash_account_id')) {
                    $table->unsignedBigInteger('cash_account_id')->nullable();
                }
                if (!Schema::hasColumn('branches', 'bank_account_id')) {
                    $table->unsignedBigInteger('bank_account_id')->nullable();
                }
                if (!Schema::hasColumn('branches', 'assigned_warehouse_id')) {
                    $table->unsignedBigInteger('assigned_warehouse_id')->nullable();
                }
                if (!Schema::hasColumn('branches', 'settings')) {
                    $table->json('settings')->nullable();
                }

                // Indexes for performance
                $table->index('branch_type');
                $table->index('manager_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                $columns = [
                    'branch_code',
                    'branch_type',
                    'manager_id',
                    'status',
                    'opening_date',
                    'opening_hours',
                    'branch_logo',
                    'description',
                    'cash_account_id',
                    'bank_account_id',
                    'assigned_warehouse_id',
                    'settings',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('branches', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
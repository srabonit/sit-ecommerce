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
        // 1. Add branch access columns to users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'primary_branch_id')) {
                    $table->unsignedBigInteger('primary_branch_id')->nullable();
                    $table->index('primary_branch_id');
                }
                if (!Schema::hasColumn('users', 'has_all_branch_access')) {
                    $table->boolean('has_all_branch_access')->default(false);
                    $table->index('has_all_branch_access');
                }
            });
        }

        // 2. Create branch_user pivot table for multi-branch assignment
        if (!Schema::hasTable('branch_user')) {
            Schema::create('branch_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('user_id');
                $table->boolean('is_primary')->default(false);
                $table->string('role_in_branch', 40)->default('staff'); // manager, cashier, sales_person, staff
                $table->boolean('can_access_pos')->default(true);
                $table->boolean('can_manage_inventory')->default(false);
                $table->boolean('can_view_reports')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['branch_id', 'user_id'], 'branch_user_unique');
                $table->index('branch_id');
                $table->index('user_id');
                $table->index('role_in_branch');
                $table->index('is_primary');
                $table->index('can_access_pos');
                $table->index('is_active');
            });
        }

        // 3. Create branch_settings table for granular operational toggles
        if (!Schema::hasTable('branch_settings')) {
            Schema::create('branch_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->string('key', 100);
                $table->longText('value')->nullable();
                $table->timestamps();

                $table->unique(['branch_id', 'key'], 'branch_settings_unique');
                $table->index('branch_id');
                $table->index('key');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_settings');
        Schema::dropIfExists('branch_user');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'primary_branch_id')) {
                    $table->dropIndex(['primary_branch_id']);
                    $table->dropColumn('primary_branch_id');
                }
                if (Schema::hasColumn('users', 'has_all_branch_access')) {
                    $table->dropIndex(['has_all_branch_access']);
                    $table->dropColumn('has_all_branch_access');
                }
            });
        }
    }
};
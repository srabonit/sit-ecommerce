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
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // 1. Email Verification (if missing from base table)
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            // 2. Phone Verification Timestamp
            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('phone');
            }

            // 3. Google OAuth Identity & Avatar
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id', 100)->nullable()->index()->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'google_avatar')) {
                $table->text('google_avatar')->nullable()->after('google_id');
            }

            // 4. Two-Factor Authentication (2FA) Controls
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('is_locked');
            }
            if (!Schema::hasColumn('users', 'two_factor_type')) {
                $table->string('two_factor_type', 20)->default('sms')->after('two_factor_enabled'); // 'sms' or 'email'
            }
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('two_factor_type');
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach ([
                'phone_verified_at',
                'google_id',
                'google_avatar',
                'two_factor_enabled',
                'two_factor_type',
                'two_factor_secret',
                'two_factor_confirmed_at'
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
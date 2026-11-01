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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'client_ip')) {
                    $table->string('client_ip', 45)->nullable()->after('payment_status')->index();
                }

                if (!Schema::hasColumn('orders', 'ip_version')) {
                    $table->enum('ip_version', ['v4', 'v6'])->default('v4')->after('client_ip');
                }

                if (!Schema::hasColumn('orders', 'device_type')) {
                    $table->string('device_type', 50)->nullable()->after('ip_version');
                }

                if (!Schema::hasColumn('orders', 'device_user_agent')) {
                    $table->string('device_user_agent', 255)->nullable()->after('device_type');
                }

                if (!Schema::hasColumn('orders', 'security_risk_score')) {
                    $table->unsignedTinyInteger('security_risk_score')->default(0)->after('device_user_agent')->index();
                }

                if (!Schema::hasColumn('orders', 'is_suspicious')) {
                    $table->boolean('is_suspicious')->default(false)->after('security_risk_score')->index();
                }

                if (!Schema::hasColumn('orders', 'suspicious_reason')) {
                    $table->string('suspicious_reason', 255)->nullable()->after('is_suspicious');
                }

                if (!Schema::hasColumn('orders', 'security_review_status')) {
                    $table->enum('security_review_status', [
                        'clean',
                        'under_review',
                        'flagged_spam',
                        'approved_by_admin',
                        'cancelled_fraud'
                    ])->default('clean')->after('suspicious_reason')->index();
                }

                if (!Schema::hasColumn('orders', 'security_checked_at')) {
                    $table->timestamp('security_checked_at')->nullable()->after('security_review_status');
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
                $columnsToDrop = [
                    'security_checked_at',
                    'security_review_status',
                    'suspicious_reason',
                    'is_suspicious',
                    'security_risk_score',
                    'device_user_agent',
                    'device_type',
                    'ip_version',
                    'client_ip',
                ];

                foreach ($columnsToDrop as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
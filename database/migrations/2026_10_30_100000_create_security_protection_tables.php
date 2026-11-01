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
        // ১. Security Settings: মাস্টার টগল ও কনফিগারেশন প্যারামিটার
        if (!Schema::hasTable('security_settings')) {
            Schema::create('security_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->longText('value')->nullable();
                $table->string('group', 50)->default('general')->index();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        // ২. Blocked IPs: একক IPv4 এবং IPv6 ব্লকিং রেকর্ড
        if (!Schema::hasTable('security_blocked_ips')) {
            Schema::create('security_blocked_ips', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->index();
                $table->enum('ip_version', ['v4', 'v6'])->default('v4');
                $table->enum('block_type', ['temporary', 'permanent'])->default('temporary')->index();
                $table->string('block_reason', 255)->nullable();
                $table->string('block_category', 100)->default('manual')->index(); // brute_force, multiple_orders, spam_form, url_attack, manual
                $table->timestamp('start_at')->useCurrent();
                $table->timestamp('expires_at')->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->text('admin_note')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['ip_address', 'is_active', 'expires_at'], 'sec_blocked_ip_lookup_idx');
            });
        }

        // ৩. Blocked Networks: CIDR ও নেটওয়ার্ক সাবনেট রেঞ্জ ব্লকিং
        if (!Schema::hasTable('security_blocked_networks')) {
            Schema::create('security_blocked_networks', function (Blueprint $table) {
                $table->id();
                $table->string('cidr_range', 50)->unique();
                $table->enum('ip_version', ['v4', 'v6'])->default('v4');
                $table->string('start_ip', 45)->nullable();
                $table->string('end_ip', 45)->nullable();
                $table->unsignedBigInteger('estimated_ip_count')->default(0);
                $table->string('block_reason', 255)->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->text('admin_note')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'expires_at'], 'sec_blocked_cidr_lookup_idx');
            });
        }

        // ৪. Security Allowlists: বিশ্বস্ত আইপি, অফিস নেটওয়ার্ক, গেটওয়ে কলব্যাক ও রিকভারি আইপি
        if (!Schema::hasTable('security_allowlists')) {
            Schema::create('security_allowlists', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['ip', 'cidr', 'endpoint'])->default('ip')->index();
                $table->string('value', 191)->index();
                $table->string('label', 150)->nullable();
                $table->string('exemption_scope', 50)->default('all'); // all, firewall_only, rate_limit_only
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // ৫. Security IP Logs: অ্যাক্টিভিটি হিস্ট্রি ও ট্র্যাকিং লগ (পারফর্ম্যান্স অপ্টিমাইজড ইনডেক্সিং)
        if (!Schema::hasTable('security_ip_logs')) {
            Schema::create('security_ip_logs', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->index();
                $table->enum('ip_version', ['v4', 'v6'])->default('v4');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->enum('user_type', ['guest', 'customer', 'staff', 'admin'])->default('guest');
                $table->string('session_id', 100)->nullable()->index();
                $table->string('device_type', 50)->nullable(); // Desktop, Mobile, Tablet
                $table->string('browser', 80)->nullable();
                $table->string('platform', 80)->nullable(); // OS
                $table->string('user_agent', 255)->nullable();
                $table->string('request_method', 10)->default('GET');
                $table->text('request_url')->nullable();
                $table->string('url_path', 191)->nullable()->index();
                $table->text('referrer_url')->nullable();
                $table->unsignedSmallInteger('response_status')->default(200)->index();
                $table->string('activity_type', 100)->index(); // visit, checkout, order, login_success, login_failed, form_submit
                $table->unsignedTinyInteger('risk_score')->default(0)->index();
                $table->enum('security_status', ['allowed', 'flagged', 'blocked', 'challenged'])->default('allowed')->index();
                $table->string('country', 100)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('isp', 150)->nullable();
                $table->json('metadata')->nullable(); // related_order_id, form_id, etc.
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['ip_address', 'activity_type', 'created_at'], 'sec_ip_activity_perf_idx');
            });
        }

        // ৬. Security Alerts: অ্যাডমিন নোটিফিকেশন ও সাসপিশাস সিকিউরিটি ইভেন্ট
        if (!Schema::hasTable('security_alerts')) {
            Schema::create('security_alerts', function (Blueprint $table) {
                $table->id();
                $table->string('event_type', 100)->index(); // multiple_orders, multiple_forms, brute_force, high_risk_ip, url_attack
                $table->string('ip_address', 45)->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
                $table->string('title', 255);
                $table->text('message');
                $table->text('affected_url')->nullable();
                $table->unsignedInteger('attempt_count')->default(1);
                $table->unsignedBigInteger('related_id')->nullable();
                $table->string('related_type', 100)->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->boolean('email_sent')->default(false);
                $table->timestamp('email_sent_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['event_type', 'ip_address', 'created_at'], 'sec_alert_cooldown_idx');
            });
        }

        // ৭. Security URL Attacks: আক্রমণ ও স্ক্যান হওয়া URL স্ট্যাটিস্টিকস
        if (!Schema::hasTable('security_url_attacks')) {
            Schema::create('security_url_attacks', function (Blueprint $table) {
                $table->id();
                $table->string('url_path', 191)->unique();
                $table->string('request_method', 10)->default('GET');
                $table->unsignedBigInteger('total_requests')->default(1);
                $table->unsignedBigInteger('failed_requests')->default(0);
                $table->unsignedBigInteger('blocked_requests')->default(0);
                $table->unsignedInteger('unique_ips_count')->default(1);
                $table->timestamp('last_attack_at')->nullable()->index();
                $table->text('sample_suspicious_payload')->nullable();
                $table->timestamps();
            });
        }

        // ৮. Security User Restrictions: ইউজার ভিত্তিক সতর্কতা, লকআউট ও রেস্ট্রিকশন হিস্ট্রি
        if (!Schema::hasTable('security_user_restrictions')) {
            Schema::create('security_user_restrictions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->enum('restriction_type', [
                    'warning',
                    'temporary_lock',
                    'permanent_suspension',
                    'login_restricted',
                    'checkout_restricted',
                    'form_restricted'
                ])->index();
                $table->string('reason', 255)->nullable();
                $table->text('admin_note')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_active', 'expires_at'], 'sec_user_active_restriction_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_user_restrictions');
        Schema::dropIfExists('security_url_attacks');
        Schema::dropIfExists('security_alerts');
        Schema::dropIfExists('security_ip_logs');
        Schema::dropIfExists('security_allowlists');
        Schema::dropIfExists('security_blocked_networks');
        Schema::dropIfExists('security_blocked_ips');
        Schema::dropIfExists('security_settings');
    }
};
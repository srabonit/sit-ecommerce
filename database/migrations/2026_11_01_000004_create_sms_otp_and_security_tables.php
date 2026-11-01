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
        // 1. Dynamic OTP Verification Tracking
        Schema::create('sms_otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 20)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('otp_hash')->nullable(); // Plain OTP code is NEVER stored
            $table->string('provider_otp_id')->nullable(); // For Onecodesoft Managed Identity
            $table->string('provider_message_id')->nullable();
            $table->string('mode')->default('custom'); // 'identity' or 'custom'
            $table->string('purpose', 50)->default('login'); // login, register, password_reset, 2fa, phone_update
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('resend_count')->default(0);
            $table->boolean('is_verified')->default(false)->index();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('next_resend_allowed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index(); // Fixed: Added nullable() to prevent MySQL 1067 strict mode error
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 2. Global Spam & Blacklist Protection (Phone, IP, Spam Words)
        Schema::create('sms_blacklists', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('phone'); // phone, ip, word
            $table->string('value')->index(); // phone number, IP address, or spam keyword
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_blacklists');
        Schema::dropIfExists('sms_otp_verifications');
    }
};
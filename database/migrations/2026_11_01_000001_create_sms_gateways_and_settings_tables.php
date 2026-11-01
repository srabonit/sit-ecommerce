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
        // 1. Dynamic SMS Gateways / Providers Configuration
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. 'Onecodesoft', 'Custom Gateway'
            $table->string('driver')->default('onecodesoft');
            $table->string('base_url')->default('https://api.onecodesoft.com/api');
            $table->string('api_version')->default('v3');
            $table->text('api_token'); // Encrypted Bearer Token
            $table->string('sender_id')->nullable(); // Default/Approved Sender ID
            $table->enum('sender_type', ['masking', 'non_masking'])->default('non_masking');
            $table->string('business_name')->nullable(); // Required for Onecodesoft Identity OTP
            $table->decimal('price_per_sms', 8, 4)->default(0.3500);
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->unsignedInteger('estimated_sms')->default(0);
            $table->boolean('sandbox_mode')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamp('last_balance_synced_at')->nullable();
            $table->json('extra_metadata')->nullable();
            $table->timestamps();
        });

        // 2. Global SMS & Subservice Independent Master Controls
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general, otp, auth, security, marketing
            $table->string('type')->default('string'); // boolean, integer, json, string
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
        Schema::dropIfExists('sms_providers');
    }
};
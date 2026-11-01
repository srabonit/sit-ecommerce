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
        // 1. Dynamic Event-driven SMS Templates
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_key')->unique(); // e.g. 'order_confirmation', 'login_otp', 'order_shipped'
            $table->string('name');
            $table->string('event_type')->index(); // customer, order, support, admin, auth
            $table->string('sender_id')->nullable();
            $table->enum('sender_type', ['auto', 'masking', 'non_masking'])->default('auto');
            $table->text('message');
            $table->json('available_variables')->nullable(); // e.g. ["customer_name", "order_id", "order_total"]
            $table->boolean('is_active')->default(true);
            $table->boolean('is_unicode')->default(false);
            $table->timestamps();
        });

        // 2. Comprehensive SMS Delivery Logs & Audit Trail
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient', 20)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->string('sms_type', 50)->default('transactional')->index(); // transactional, otp, marketing
            $table->string('provider', 50)->default('onecodesoft');
            $table->string('sender_id')->nullable();
            $table->string('message_id')->nullable()->index(); // Provider API message ID
            $table->text('message_content');
            $table->unsignedSmallInteger('characters_count')->default(0);
            $table->unsignedSmallInteger('sms_parts')->default(1);
            $table->decimal('cost', 8, 4)->default(0.0000);
            $table->enum('status', [
                'queued', 'processing', 'sent', 'delivered', 'failed', 'rejected', 'blocked', 'spam', 'cancelled'
            ])->default('queued')->index();
            $table->string('status_code', 50)->nullable();
            $table->text('gateway_response')->nullable();
            $table->boolean('is_sandbox')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_templates');
    }
};
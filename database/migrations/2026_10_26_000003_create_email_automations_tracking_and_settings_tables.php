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
        // 1. Email Provider & SMTP Settings Table (Encrypted Credentials)
        if (!Schema::hasTable('email_provider_settings')) {
            Schema::create('email_provider_settings', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Primary Provider');
                $table->enum('driver', ['smtp', 'ses', 'brevo', 'mailgun', 'sendgrid', 'postmark'])->default('smtp')->index();
                $table->boolean('is_active')->default(false)->index();
                
                // Connection Configuration
                $table->string('host')->nullable();
                $table->integer('port')->default(587);
                $table->string('encryption')->default('tls');
                $table->string('username')->nullable();
                $table->text('password')->nullable()->comment('Encrypted password/secret');
                $table->text('api_key')->nullable()->comment('Encrypted API key');
                $table->string('region')->nullable()->comment('For AWS SES');
                
                // Default Mail Identity
                $table->string('sender_name')->nullable();
                $table->string('sender_email')->nullable();
                $table->string('reply_to')->nullable();

                // Rate Limiting & Throttling
                $table->unsignedInteger('hourly_limit')->default(500);
                $table->unsignedInteger('daily_limit')->default(10000);
                $table->unsignedInteger('batch_size')->default(100);
                
                $table->timestamps();
            });
        }

        // 2. Email Automations Table (Triggers & Lifecycle Workflows)
        if (!Schema::hasTable('email_automations')) {
            Schema::create('email_automations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                
                // Trigger Event Definition
                $table->enum('trigger_event', [
                    'customer_registered',
                    'order_placed',
                    'order_delivered',
                    'cart_abandoned',
                    'wishlist_added',
                    'product_published',
                    'product_back_in_stock',
                    'birthday',
                    'inactive_days',
                    'custom_webhook'
                ])->index();
                
                $table->json('trigger_conditions')->nullable()->comment('JSON filters: min_order_amount, category_id, inactive_days, etc.');
                $table->boolean('is_active')->default(false)->index();
                
                // Performance counters
                $table->unsignedInteger('total_triggered')->default(0);
                $table->unsignedInteger('total_completed')->default(0);
                
                $table->unsignedBigInteger('created_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 3. Email Automation Steps (Sequential Trigger -> Delay -> Action Steps)
        if (!Schema::hasTable('email_automation_steps')) {
            Schema::create('email_automation_steps', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('automation_id')->index();
                $table->unsignedInteger('step_order')->default(1);
                
                // Step Behavior
                $table->enum('step_type', ['send_email', 'delay', 'condition_check'])->default('send_email');
                $table->unsignedInteger('delay_minutes')->default(0)->comment('Wait duration in minutes before running this step');
                
                // Mail Content
                $table->unsignedBigInteger('template_id')->nullable()->index();
                $table->string('subject')->nullable();
                $table->string('preview_text')->nullable();
                $table->unsignedBigInteger('coupon_id')->nullable();
                $table->json('step_conditions')->nullable()->comment('Conditional rules to skip step if order already paid/cancelled');
                
                $table->timestamps();

                $table->foreign('automation_id')->references('id')->on('email_automations')->onDelete('cascade');
                $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
            });
        }

        // 4. Abandoned Carts Persistent Tracker
        if (!Schema::hasTable('email_abandoned_carts')) {
            Schema::create('email_abandoned_carts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('email')->index();
                $table->string('phone')->nullable();
                $table->string('customer_name')->nullable();
                
                // Cart Data Snapshot
                $table->json('cart_items')->comment('Snapshot of products, quantities, prices, variants, images');
                $table->decimal('cart_value', 14, 2)->default(0.00);
                
                // Secure 64-char Unique Recovery Token & URL
                $table->string('recovery_token', 64)->unique();
                $table->timestamp('abandoned_at')->index();
                
                // Recovery Status
                $table->boolean('is_recovered')->default(false)->index();
                $table->timestamp('recovered_at')->nullable();
                $table->unsignedBigInteger('recovered_order_id')->nullable()->index();
                
                // Dispatch tracking
                $table->unsignedInteger('reminder_sent_count')->default(0);
                $table->timestamp('last_reminder_at')->nullable();
                
                $table->timestamps();
            });
        }

        // 5. Automation Execution & Idempotency Logs (Anti-Spam Multi-Send Protection)
        if (!Schema::hasTable('email_automation_logs')) {
            Schema::create('email_automation_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('automation_id')->index();
                $table->unsignedBigInteger('step_id')->index();
                $table->unsignedBigInteger('subscriber_id')->nullable()->index();
                $table->string('email')->index();
                
                // Context References
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('abandoned_cart_id')->nullable()->index();
                $table->string('idempotency_key', 128)->unique()->comment('Hash of automation_id + step_id + context to prevent duplicate sends');
                
                // Dispatch State
                $table->enum('status', ['pending', 'sent', 'failed', 'skipped', 'cancelled'])->default('pending')->index();
                $table->text('error_message')->nullable();
                $table->timestamp('executed_at')->nullable();
                $table->timestamps();

                $table->foreign('automation_id')->references('id')->on('email_automations')->onDelete('cascade');
                $table->foreign('step_id')->references('id')->on('email_automation_steps')->onDelete('cascade');
            });
        }

        // 6. Email Tracking Events Table (Opens, Clicks, Bounces, Spam Complaints)
        if (!Schema::hasTable('email_tracking_events')) {
            Schema::create('email_tracking_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id')->nullable()->index();
                $table->unsignedBigInteger('automation_id')->nullable()->index();
                $table->unsignedBigInteger('recipient_id')->nullable()->index();
                $table->string('email')->index();
                
                // Event Type
                $table->enum('event_type', ['open', 'click', 'bounce', 'complaint', 'unsubscribe'])->index();
                $table->string('target_url', 1000)->nullable()->comment('Clicked URL destination');
                
                // Visitor Audit Information
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('event_meta')->nullable()->comment('Raw provider webhook payload or UTM parameters');
                
                $table->timestamp('created_at')->useCurrent()->index();

                $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_tracking_events');
        Schema::dropIfExists('email_automation_logs');
        Schema::dropIfExists('email_abandoned_carts');
        Schema::dropIfExists('email_automation_steps');
        Schema::dropIfExists('email_automations');
        Schema::dropIfExists('email_provider_settings');
    }
};
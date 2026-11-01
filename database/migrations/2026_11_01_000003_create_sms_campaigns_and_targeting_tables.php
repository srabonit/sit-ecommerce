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
        // 1. Bulk SMS & Multi-channel Campaigns
        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('channel', ['sms_only', 'email_only', 'sms_and_email'])->default('sms_only');
            $table->unsignedBigInteger('sms_provider_id')->nullable();
            $table->string('sender_id')->nullable();
            $table->enum('sender_type', ['auto', 'masking', 'non_masking'])->default('auto');
            $table->string('target_audience_type')->default('all_customers');
            $table->json('targeting_filters')->nullable(); // Advanced Customer Segmentation Rules
            $table->text('sms_message')->nullable();
            $table->unsignedBigInteger('email_template_id')->nullable();
            $table->string('email_subject')->nullable();
            $table->longText('email_content')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('processed_recipients')->default(0);
            $table->unsignedInteger('successful_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->decimal('estimated_cost', 10, 4)->default(0.0000);
            $table->decimal('actual_cost', 10, 4)->default(0.0000);
            $table->enum('status', ['draft', 'scheduled', 'processing', 'completed', 'paused', 'cancelled'])->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 2. Campaign Individual Recipients Dispatch Queue
        Schema::create('sms_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('phone_number', 20)->index();
            $table->string('email')->nullable();
            $table->text('personalized_sms')->nullable();
            $table->string('sms_message_id')->nullable();
            $table->enum('sms_status', ['pending', 'sent', 'delivered', 'failed', 'blocked', 'skipped'])->default('pending')->index();
            $table->enum('email_status', ['pending', 'sent', 'delivered', 'failed', 'bounced', 'skipped'])->default('pending')->index();
            $table->text('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('sms_campaigns')->onDelete('cascade');
        });

        // 3. Independent Channel Preferences (Separate SMS & Email Unsubscribe)
        Schema::create('communication_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('phone_number', 20)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->boolean('sms_marketing_subscribed')->default(true);
            $table->boolean('email_marketing_subscribed')->default(true);
            $table->boolean('transactional_sms_allowed')->default(true);
            $table->timestamp('sms_unsubscribed_at')->nullable();
            $table->timestamp('email_unsubscribed_at')->nullable();
            $table->string('unsubscription_reason')->nullable();
            $table->string('consent_source')->default('checkout_or_register');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_preferences');
        Schema::dropIfExists('sms_campaign_recipients');
        Schema::dropIfExists('sms_campaigns');
    }
};
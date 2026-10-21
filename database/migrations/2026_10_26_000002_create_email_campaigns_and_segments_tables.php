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
        // 1. Email Templates Table (Drag & Drop / HTML Storage)
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category', 100)->default('marketing')->comment('marketing, newsletter, promotion, order, cart');
                $table->string('subject')->nullable();
                $table->json('builder_data')->nullable()->comment('Raw JSON blocks for drag-and-drop editor');
                $table->longText('html_content')->nullable()->comment('Compiled responsive HTML output');
                $table->text('plain_text')->nullable();
                $table->string('thumbnail')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 2. Audience Segments Table
        if (!Schema::hasTable('email_segments')) {
            Schema::create('email_segments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('type', ['dynamic', 'snapshot'])->default('dynamic')->comment('Dynamic updates automatically, snapshot is frozen');
                $table->json('filter_criteria')->comment('Serialized criteria: purchase history, cart, wishlist, category, inactive days');
                $table->unsignedInteger('matching_subscribers_count')->default(0);
                $table->timestamp('last_calculated_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 3. Email Campaigns Table
        if (!Schema::hasTable('email_campaigns')) {
            Schema::create('email_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('subject');
                $table->string('preview_text')->nullable();
                $table->string('sender_name')->nullable();
                $table->string('sender_email')->nullable();
                $table->string('reply_to')->nullable();

                // Campaign Scope & Type
                $table->enum('campaign_type', ['regular', 'product_targeted', 'cart_recovery', 'wishlist', 'lifecycle', 'ab_test'])
                      ->default('regular')
                      ->index();
                $table->unsignedBigInteger('template_id')->nullable()->index();
                $table->unsignedBigInteger('segment_id')->nullable()->index();
                $table->unsignedBigInteger('coupon_id')->nullable()->index();

                // Workflow & Status
                $table->enum('status', ['draft', 'scheduled', 'queued', 'sending', 'completed', 'paused', 'cancelled', 'failed'])
                      ->default('draft')
                      ->index();

                // Dispatch & Scheduling
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedInteger('batch_size')->default(100);
                $table->unsignedInteger('rate_limit_per_minute')->default(60);

                // Counters & Metrics (Cache for high performance)
                $table->unsignedInteger('total_recipients')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('delivered_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->unsignedInteger('opened_count')->default(0);
                $table->unsignedInteger('clicked_count')->default(0);
                $table->unsignedInteger('unsubscribed_count')->default(0);
                $table->unsignedInteger('bounced_count')->default(0);
                $table->decimal('total_revenue', 14, 2)->default(0.00);

                // Tracking Options
                $table->boolean('track_opens')->default(true);
                $table->boolean('track_clicks')->default(true);
                $table->string('utm_campaign')->nullable();

                $table->unsignedBigInteger('created_by')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
                $table->foreign('segment_id')->references('id')->on('email_segments')->onDelete('set null');
            });
        }

        // 4. Email Campaign Recipients (Per-subscriber delivery & audit log)
        if (!Schema::hasTable('email_campaign_recipients')) {
            Schema::create('email_campaign_recipients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id')->index();
                $table->unsignedBigInteger('subscriber_id')->nullable()->index();
                $table->string('email')->index();

                // State
                $table->enum('status', ['queued', 'sending', 'sent', 'delivered', 'failed', 'bounced', 'skipped'])
                      ->default('queued')
                      ->index();

                // Cryptographic token for individual open/click tracking
                $table->string('tracking_token', 64)->unique();

                // Engagement tracking
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->unsignedInteger('open_count')->default(0);
                $table->timestamp('clicked_at')->nullable();
                $table->unsignedInteger('click_count')->default(0);
                $table->text('error_message')->nullable();

                $table->timestamps();

                // Foreign key constraints & compound uniqueness for duplicate prevention
                $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('cascade');
                $table->unique(['campaign_id', 'email'], 'unique_campaign_recipient');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_segments');
        Schema::dropIfExists('email_templates');
    }
};
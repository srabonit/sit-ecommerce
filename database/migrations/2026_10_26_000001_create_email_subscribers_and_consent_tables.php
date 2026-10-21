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
        // 1. Email Subscribers Table
        if (!Schema::hasTable('email_subscribers')) {
            Schema::create('email_subscribers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index()->comment('Linked user id if registered');
                $table->string('email')->unique();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('phone')->nullable()->index();
                
                // Subscription Status
                $table->enum('status', ['subscribed', 'unsubscribed', 'bounced', 'complained', 'pending'])
                      ->default('subscribed')
                      ->index();
                
                // Consent Details
                $table->boolean('marketing_consent')->default(true)->index();
                $table->timestamp('consent_at')->nullable();
                $table->string('consent_source', 100)->default('checkout')->comment('checkout, popup, footer, pos, manual, registration');
                $table->string('consent_ip', 45)->nullable();
                
                // Secure 64-char Unique Unsubscribe Token
                $table->string('unsubscribe_token', 64)->unique();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->text('unsubscribe_reason')->nullable();
                
                // Soft deletes & timestamps
                $table->softDeletes();
                $table->timestamps();

                // Composite Index for high-performance audience filtering
                $table->index(['status', 'marketing_consent']);
            });
        }

        // 2. Email Consent Audit Logs Table (Legal Compliance)
        if (!Schema::hasTable('email_consent_logs')) {
            Schema::create('email_consent_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subscriber_id')->nullable()->index();
                $table->string('email')->index();
                $table->enum('action', ['opt_in', 'opt_out', 'resubscribe', 'consent_updated']);
                $table->string('source', 100)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('payload')->nullable()->comment('Snapshot of consent form/context');
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('subscriber_id')
                      ->references('id')
                      ->on('email_subscribers')
                      ->onDelete('cascade');
            });
        }

        // 3. Email Suppression & Blacklist Table (Spam & Hard Bounce Safeguard)
        if (!Schema::hasTable('email_suppression_list')) {
            Schema::create('email_suppression_list', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->enum('reason', ['hard_bounce', 'soft_bounce_limit', 'spam_complaint', 'manual_block', 'unsubscribe'])
                      ->default('hard_bounce')
                      ->index();
                $table->text('details')->nullable();
                $table->unsignedBigInteger('blocked_by')->nullable()->comment('Admin user id if manually blocked');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_consent_logs');
        Schema::dropIfExists('email_suppression_list');
        Schema::dropIfExists('email_subscribers');
    }
};
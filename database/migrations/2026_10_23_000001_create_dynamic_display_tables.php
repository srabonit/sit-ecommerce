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
        // 1. Dynamic Popups & Promotional Modals
        if (!Schema::hasTable('dynamic_popups')) {
            Schema::create('dynamic_popups', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('popup_type')->default('promotion'); // welcome, promotion, newsletter, offer, custom_html
                $table->longText('content')->nullable();
                $table->string('image')->nullable();
                $table->string('button_text')->nullable();
                $table->string('button_url')->nullable();
                $table->string('trigger_type')->default('delay'); // delay, scroll, immediate, exit_intent
                $table->integer('trigger_value')->default(3); // e.g., 3 seconds delay or 50% scroll
                $table->integer('frequency_days')->default(1); // Cookie frequency in days
                $table->boolean('is_active')->default(false); // Inactive by default for safety
                $table->json('settings')->nullable(); // Colors, modal width, custom styles
                $table->timestamps();
            });
        }

        // 2. Floating Contact & Social Action Widgets
        if (!Schema::hasTable('floating_widgets')) {
            Schema::create('floating_widgets', function (Blueprint $table) {
                $table->id();
                $table->string('widget_type'); // whatsapp, phone, messenger, email, custom_link
                $table->string('label')->nullable();
                $table->string('value'); // Phone number, WhatsApp number, or target URL
                $table->string('icon')->nullable();
                $table->string('position')->default('bottom_right'); // bottom_right, bottom_left
                $table->integer('bottom_offset')->default(20); // Base bottom offset in px
                $table->integer('side_offset')->default(20); // Side offset in px
                $table->string('bg_color')->nullable();
                $table->string('icon_color')->nullable();
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(false); // Inactive by default
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        // 3. Centralized Display & Scheduling Rules (Polymorphic Support)
        if (!Schema::hasTable('display_campaign_rules')) {
            Schema::create('display_campaign_rules', function (Blueprint $table) {
                $table->id();
                $table->nullableMorphs('ruleable'); // Can attach to popups, widgets, or custom campaigns
                $table->string('rule_scope')->default('custom'); // popup, widget, sticky_footer_bar
                $table->json('device_visibility')->nullable(); // {"desktop": true, "tablet": true, "mobile": true}
                $table->string('page_target_type')->default('all'); // all, home_only, products_only, specific_pages
                $table->json('target_pages')->nullable(); // ["shop", "checkout", "product/*"]
                $table->json('excluded_pages')->nullable(); // ["cart", "checkout"]
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->string('user_type')->default('all'); // all, guest, logged_in
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_campaign_rules');
        Schema::dropIfExists('floating_widgets');
        Schema::dropIfExists('dynamic_popups');
    }
};
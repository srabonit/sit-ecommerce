<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_settings')) {
            Schema::create('notification_settings', function (Blueprint $table) {
                $table->id();
                $table->string('type'); // 'smtp' or 'sms'
                $table->string('provider')->default('custom'); // 'smtp', 'greenweb', 'twilio'
                $table->json('credentials'); // API keys, SMTP host/port/user/pass, Sender ID
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
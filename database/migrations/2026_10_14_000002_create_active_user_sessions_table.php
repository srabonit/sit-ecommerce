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
        if (!Schema::hasTable('active_user_sessions')) {
            Schema::create('active_user_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 191)->unique()->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('device_type', 30)->default('Desktop'); // Desktop, Mobile, Tablet
                $table->string('browser', 50)->nullable();
                $table->string('platform', 50)->nullable(); // Windows, macOS, Linux, Android, iOS
                $table->timestamp('last_activity')->nullable()->index();
                $table->timestamp('login_at')->nullable();
                $table->boolean('is_revoked')->default(false)->index();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_user_sessions');
    }
};
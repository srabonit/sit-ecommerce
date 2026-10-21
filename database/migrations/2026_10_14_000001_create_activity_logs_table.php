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
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name', 100)->nullable();
                $table->string('role', 50)->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('action', 50)->index(); // login, create, update, delete, status_change, etc.
                $table->string('module', 60)->index(); // orders, products, courier, users, settings, etc.
                $table->string('record_type', 100)->nullable(); // App\Models\Order, etc.
                $table->string('record_id', 50)->nullable()->index();
                $table->string('record_ref', 100)->nullable()->index(); // Invoice No, SKU, etc.
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('device_type', 30)->nullable(); // Desktop, Mobile, Tablet
                $table->string('result', 20)->default('success')->index(); // success, failed
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
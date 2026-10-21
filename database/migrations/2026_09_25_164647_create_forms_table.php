<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->string('admin_email')->nullable();

            $table->string('email_subject')->nullable();

            $table->text('success_message')
                ->nullable()
                ->default('Your message has been sent successfully.');

            $table->string('redirect_url')->nullable();

            $table->boolean('is_active')->default(true);

            $table->boolean('save_submissions')->default(true);

            $table->boolean('send_admin_email')->default(true);

            $table->boolean('send_user_email')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
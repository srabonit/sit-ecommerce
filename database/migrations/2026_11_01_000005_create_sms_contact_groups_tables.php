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
        // 1. SMS Contact Groups (e.g. VIP Customers, Wholesalers, Event Attendees)
        Schema::create('sms_contact_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('total_contacts')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 2. Contact Group Members (Imported or Manually Added Numbers)
        Schema::create('sms_contact_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->index();
            $table->string('phone_number', 20)->index();
            $table->string('name')->nullable();
            $table->json('custom_attributes')->nullable(); // Extra variables for personalization: e.g. {"company":"ABC"}
            $table->boolean('is_valid')->default(true);
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('sms_contact_groups')->onDelete('cascade');
            $table->unique(['group_id', 'phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_contact_group_members');
        Schema::dropIfExists('sms_contact_groups');
    }
};
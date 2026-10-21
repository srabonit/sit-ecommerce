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
        if (!Schema::hasTable('courier_bookings')) {
            Schema::create('courier_bookings', function (Blueprint $table) {
                $table->id();
                
                // ১. অর্ডার লিঙ্ক (Relational Foreign Key)
                $table->unsignedBigInteger('order_id')->index();
                
                // ২. কুরিয়ার প্রোভাইডার তথ্য
                $table->string('courier_name', 100); // steadfast, pathao, redx, manual
                $table->string('courier_type', 50)->default('api'); // api, manual
                $table->string('consignment_id', 150)->nullable()->index();
                $table->string('tracking_code', 150)->index();
                $table->string('service_type', 50)->default('regular'); // regular, express, same_day
                $table->decimal('parcel_weight', 8, 2)->default(0.50); // ওজনের হিসাব (কেজি)
                
                // ৩. আর্থিক ও সিওডি হিসাব (Financial & COD Tracking)
                $table->decimal('order_amount', 12, 2)->default(0.00); // মোট অর্ডার মূল্য
                $table->decimal('cod_amount', 12, 2)->default(0.00);   // কুরিয়ার থেকে কালেকশনযোগ্য COD
                $table->decimal('collected_amount', 12, 2)->default(0.00); // কাস্টমার যা পে করেছে
                $table->decimal('cod_charge', 10, 2)->default(0.00);   // কুরিয়ার সিওডি কমিশন ফি
                
                // ৪. কুরিয়ার ডেলিভারি চার্জ হিসাব (Courier Service Cost)
                $table->decimal('courier_cost', 10, 2)->default(0.00);      // কুরিয়ার চার্জ
                $table->decimal('courier_cost_paid', 10, 2)->default(0.00); // কুরিয়ারকে পরিশোধিত চার্জ
                $table->decimal('courier_cost_due', 10, 2)->default(0.00);  // কুরিয়ার বকেয়া চার্জ
                $table->decimal('return_cost', 10, 2)->default(0.00);        // পার্সেল রিটার্ন ফি
                
                // ৫. স্ট্যাটাস ট্র্যাকিং (Delivery & COD Settlement Separated)
                $table->string('booking_status', 50)->default('booked')->index(); // booked, in_transit, delivered, returned, cancelled
                $table->string('delivery_status', 50)->default('in_review')->index(); // কুরিয়ার এপিআই স্ট্যাটাস
                $table->string('cod_settlement_status', 50)->default('unsettled')->index(); // unsettled, partial, settled
                
                // ৬. সিওডি সেটেলমেন্ট ও হ্যান্ডওভার তারিখ
                $table->decimal('settled_amount', 12, 2)->default(0.00); // কুরিয়ার থেকে ব্যাংক/ক্যাশ পাওয়া টাকা
                $table->timestamp('settlement_date')->nullable();
                $table->timestamp('handover_date')->nullable();
                
                // ৭. নোটস ও অডিট ইনফো
                $table->text('remarks')->nullable();
                $table->timestamps();

                // ফরেন কি সেইফটি (যদি orders টেবিল থাকে)
                if (Schema::hasTable('orders')) {
                    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_bookings');
    }
};
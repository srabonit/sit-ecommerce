<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'courier_name')) {
                $table->string('courier_name')->nullable();
            }
            if (!Schema::hasColumn('orders', 'courier_tracking_id')) {
                $table->string('courier_tracking_id')->nullable();
            }
            if (!Schema::hasColumn('orders', 'admin_note')) {
                $table->text('admin_note')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'courier_name')) {
                $table->dropColumn('courier_name');
            }
            if (Schema::hasColumn('orders', 'courier_tracking_id')) {
                $table->dropColumn('courier_tracking_id');
            }
            if (Schema::hasColumn('orders', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
        });
    }
};
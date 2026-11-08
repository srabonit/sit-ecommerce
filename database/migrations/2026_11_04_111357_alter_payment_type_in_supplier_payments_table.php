<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Direct SQL statement dile Doctrine DBAL package lagbe na ebong MySQL ENUM safely VARCHAR hoye jabe
        DB::statement("ALTER TABLE `supplier_payments` MODIFY COLUMN `payment_type` VARCHAR(100) NULL DEFAULT 'invoice_payment'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `supplier_payments` MODIFY COLUMN `payment_type` VARCHAR(50) NULL DEFAULT 'invoice_payment'");
    }
};
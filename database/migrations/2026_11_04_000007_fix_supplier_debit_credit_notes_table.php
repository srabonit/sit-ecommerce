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
        if (Schema::hasTable('supplier_debit_credit_notes')) {
            // 1. MySQL ENUM ke safe VARCHAR e rupantor
            try {
                DB::statement("ALTER TABLE `supplier_debit_credit_notes` MODIFY COLUMN `note_type` VARCHAR(50) NOT NULL DEFAULT 'debit_note'");
                DB::statement("ALTER TABLE `supplier_debit_credit_notes` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'posted'");
            } catch (\Throwable $e) {}

            // 2. Missing columns safely add kora
            Schema::table('supplier_debit_credit_notes', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier_debit_credit_notes', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('supplier_id')->index();
                }
                if (!Schema::hasColumn('supplier_debit_credit_notes', 'reference_no')) {
                    $table->string('reference_no', 100)->nullable()->after('note_number')->index();
                }
                if (!Schema::hasColumn('supplier_debit_credit_notes', 'reason_category')) {
                    $table->string('reason_category', 100)->nullable()->after('note_date');
                }
                if (!Schema::hasColumn('supplier_debit_credit_notes', 'reason')) {
                    $table->string('reason', 255)->nullable()->after('reason_category');
                }
                if (!Schema::hasColumn('supplier_debit_credit_notes', 'chart_of_account_id')) {
                    $table->unsignedBigInteger('chart_of_account_id')->nullable()->after('total_amount');
                }
                if (!Schema::hasColumn('supplier_debit_credit_notes', 'notes')) {
                    $table->text('notes')->nullable()->after('attachment_file');
                }
                if (!Schema::hasColumn('supplier_debit_credit_notes', 'cancelled_reason')) {
                    $table->string('cancelled_reason', 500)->nullable()->after('notes');
                }
            });

            // 3. Foreign key constraints with fallback check
            if (Schema::hasTable('branches')) {
                Schema::table('supplier_debit_credit_notes', function (Blueprint $table) {
                    try {
                        $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                    } catch (\Throwable $e) {}
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
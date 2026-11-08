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
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'company_name')) {
                $table->string('company_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('suppliers', 'alt_phone')) {
                $table->string('alt_phone', 50)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('suppliers', 'bin_number')) {
                $table->string('bin_number', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('suppliers', 'tax_number')) {
                $table->string('tax_number', 50)->nullable()->after('bin_number');
            }
            if (!Schema::hasColumn('suppliers', 'trade_license_no')) {
                $table->string('trade_license_no', 100)->nullable()->after('tax_number');
            }
            if (!Schema::hasColumn('suppliers', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('trade_license_no');
            }
            if (!Schema::hasColumn('suppliers', 'payment_terms')) {
                $table->string('payment_terms', 50)->default('net_30')->after('category_id');
            }
            if (!Schema::hasColumn('suppliers', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0.00)->after('payment_terms');
            }
            if (!Schema::hasColumn('suppliers', 'opening_balance')) {
                $table->decimal('opening_balance', 15, 2)->default(0.00)->after('credit_limit');
            }
            if (!Schema::hasColumn('suppliers', 'current_balance')) {
                $table->decimal('current_balance', 15, 2)->default(0.00)->after('opening_balance');
            }
            if (!Schema::hasColumn('suppliers', 'opening_balance_date')) {
                $table->date('opening_balance_date')->nullable()->after('current_balance');
            }
            if (!Schema::hasColumn('suppliers', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('opening_balance_date');
            }
            if (!Schema::hasColumn('suppliers', 'bank_branch')) {
                $table->string('bank_branch', 100)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('suppliers', 'bank_account_name')) {
                $table->string('bank_account_name', 150)->nullable()->after('bank_branch');
            }
            if (!Schema::hasColumn('suppliers', 'bank_account_no')) {
                $table->string('bank_account_no', 50)->nullable()->after('bank_account_name');
            }
            if (!Schema::hasColumn('suppliers', 'bank_routing_no')) {
                $table->string('bank_routing_no', 50)->nullable()->after('bank_account_no');
            }
            if (!Schema::hasColumn('suppliers', 'swift_code')) {
                $table->string('swift_code', 50)->nullable()->after('bank_routing_no');
            }
            if (!Schema::hasColumn('suppliers', 'mfs_number')) {
                $table->string('mfs_number', 50)->nullable()->after('swift_code');
            }
            if (!Schema::hasColumn('suppliers', 'currency')) {
                $table->string('currency', 10)->default('BDT')->after('mfs_number');
            }
            if (!Schema::hasColumn('suppliers', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'postal_code')) {
                $table->string('postal_code', 50)->nullable()->after('city');
            }
            if (!Schema::hasColumn('suppliers', 'country')) {
                $table->string('country', 100)->default('Bangladesh')->after('postal_code');
            }
            if (!Schema::hasColumn('suppliers', 'kyc_document')) {
                $table->string('kyc_document')->nullable()->after('country');
            }
            if (!Schema::hasColumn('suppliers', 'notes')) {
                $table->text('notes')->nullable()->after('kyc_document');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $columns = [
                'company_name', 'contact_person', 'alt_phone', 'bin_number', 'tax_number',
                'trade_license_no', 'category_id', 'payment_terms', 'credit_limit',
                'opening_balance', 'current_balance', 'opening_balance_date', 'bank_name',
                'bank_branch', 'bank_account_name', 'bank_account_no', 'bank_routing_no',
                'swift_code', 'mfs_number', 'currency', 'city', 'postal_code', 'country',
                'kyc_document', 'notes'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
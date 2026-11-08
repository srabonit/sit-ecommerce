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
            // Basic Contact & Address Fields
            if (!Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('suppliers', 'alternate_phone')) {
                $table->string('alternate_phone')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('suppliers', 'additional_phones')) {
                $table->json('additional_phones')->nullable()->after('alternate_phone');
            }
            if (!Schema::hasColumn('suppliers', 'website')) {
                $table->string('website')->nullable()->after('email');
            }
            if (!Schema::hasColumn('suppliers', 'country')) {
                $table->string('country')->default('Bangladesh')->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'division')) {
                $table->string('division')->nullable()->after('country');
            }
            if (!Schema::hasColumn('suppliers', 'district')) {
                $table->string('district')->nullable()->after('division');
            }
            if (!Schema::hasColumn('suppliers', 'city')) {
                $table->string('city')->nullable()->after('district');
            }
            if (!Schema::hasColumn('suppliers', 'area')) {
                $table->string('area')->nullable()->after('city');
            }
            if (!Schema::hasColumn('suppliers', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('area');
            }

            // Business & Regulatory (BIN excluded as per instruction)
            if (!Schema::hasColumn('suppliers', 'tin_number')) {
                $table->string('tin_number')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('suppliers', 'trade_license_number')) {
                $table->string('trade_license_number')->nullable()->after('tin_number');
            }
            if (!Schema::hasColumn('suppliers', 'business_type')) {
                $table->string('business_type')->nullable()->after('trade_license_number');
            }

            // Banking & Settlement Info
            if (!Schema::hasColumn('suppliers', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('business_type');
            }
            if (!Schema::hasColumn('suppliers', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('suppliers', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_account_name');
            }
            if (!Schema::hasColumn('suppliers', 'bank_branch')) {
                $table->string('bank_branch')->nullable()->after('bank_account_number');
            }
            if (!Schema::hasColumn('suppliers', 'bank_routing_number')) {
                $table->string('bank_routing_number')->nullable()->after('bank_branch');
            }

            // Financial & Credit Controls
            if (!Schema::hasColumn('suppliers', 'credit_limit')) {
                $table->decimal('credit_limit', 14, 2)->default(0.00)->after('bank_routing_number');
            }
            if (!Schema::hasColumn('suppliers', 'credit_period_days')) {
                $table->unsignedInteger('credit_period_days')->default(0)->after('credit_limit');
            }
            if (!Schema::hasColumn('suppliers', 'opening_balance')) {
                $table->decimal('opening_balance', 14, 2)->default(0.00)->after('credit_period_days');
            }
            if (!Schema::hasColumn('suppliers', 'opening_balance_type')) {
                $table->enum('opening_balance_type', ['payable', 'advance'])->default('payable')->after('opening_balance');
            }
            if (!Schema::hasColumn('suppliers', 'opening_balance_date')) {
                $table->date('opening_balance_date')->nullable()->after('opening_balance_type');
            }
            if (!Schema::hasColumn('suppliers', 'default_payment_method')) {
                $table->string('default_payment_method')->nullable()->after('opening_balance_date');
            }

            // Status & Notes
            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended', 'blacklisted'])->default('active')->after('default_payment_method');
            }
            if (!Schema::hasColumn('suppliers', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $columnsToDrop = [
                'contact_person',
                'alternate_phone',
                'additional_phones',
                'website',
                'country',
                'division',
                'district',
                'city',
                'area',
                'postal_code',
                'tin_number',
                'trade_license_number',
                'business_type',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_branch',
                'bank_routing_number',
                'credit_limit',
                'credit_period_days',
                'opening_balance',
                'opening_balance_type',
                'opening_balance_date',
                'default_payment_method',
                'status',
                'internal_notes',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('suppliers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
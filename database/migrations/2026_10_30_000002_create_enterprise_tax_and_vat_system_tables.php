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
        // =========================================================================
        // ১. TAX CLASSES TABLE (Standard, Reduced, Zero-rated, Exempt, Services)
        // =========================================================================
        if (!Schema::hasTable('tax_classes')) {
            Schema::create('tax_classes', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 50)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        // =========================================================================
        // ২. TAX RULES TABLE (Dynamic Rules with Priority, Inclusive/Exclusive)
        // =========================================================================
        if (!Schema::hasTable('tax_rules')) {
            Schema::create('tax_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tax_class_id')->nullable()->constrained('tax_classes')->nullOnDelete();
                $table->string('name', 150);
                $table->string('code', 50)->unique();
                $table->enum('tax_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('rate', 8, 4)->default(0.0000); // 5.0000, 7.5000, 15.0000
                $table->decimal('fixed_amount', 12, 2)->default(0.00);
                $table->enum('calculation_type', ['exclusive', 'inclusive'])->default('exclusive');
                $table->enum('status_type', ['taxable', 'exempt', 'zero_rated'])->default('taxable');
                $table->integer('priority')->default(1); // Higher number = higher precedence
                $table->decimal('min_taxable_amount', 12, 2)->default(0.00);
                $table->decimal('max_taxable_amount', 12, 2)->nullable();
                $table->dateTime('valid_from')->nullable();
                $table->dateTime('valid_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('admin_note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['tax_class_id', 'is_active', 'priority']);
            });
        }

        // =========================================================================
        // ৩. TAX RULE CONDITIONS (Mapping with Shortened Custom Index Identifier)
        // =========================================================================
        if (!Schema::hasTable('tax_rule_conditions')) {
            Schema::create('tax_rule_conditions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tax_rule_id')->constrained('tax_rules')->cascadeOnDelete();
                $table->enum('condition_type', [
                    'global',
                    'product',
                    'variant',
                    'category',
                    'brand',
                    'branch',
                    'shipping_zone',
                    'delivery_city',
                    'delivery_area',
                    'customer_group',
                    'delivery_charge',
                    'service_fee',
                    'commission'
                ])->default('global');
                $table->unsignedBigInteger('condition_id')->nullable(); // Product ID, Cat ID, Branch ID etc.
                $table->string('condition_value', 100)->nullable(); // City Name or Zone code
                $table->boolean('is_excluded')->default(false);
                $table->timestamps();

                // 64-character identifier limit fix with explicit short index name
                $table->index(['tax_rule_id', 'condition_type', 'condition_id'], 'trc_rule_type_id_idx');
            });
        }

        // =========================================================================
        // ৪. TAX PAYMENTS TABLE (Government Remittance & Treasury Challan Ledger)
        // =========================================================================
        if (!Schema::hasTable('tax_payments')) {
            Schema::create('tax_payments', function (Blueprint $table) {
                $table->id();
                $table->date('payment_date');
                $table->string('tax_period', 50); // October 2026 or 2026-Q4
                $table->decimal('amount', 14, 2);
                $table->string('payment_method', 50)->default('bank_transfer');
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->string('challan_number', 100)->nullable();
                $table->string('mushak_reference', 100)->nullable();
                $table->string('document_file', 255)->nullable();
                $table->text('notes')->nullable();
                $table->enum('payment_status', ['completed', 'pending', 'rejected'])->default('completed');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['payment_date', 'tax_period']);
            });
        }

        // =========================================================================
        // ৫. TAX ADJUSTMENTS TABLE (Order Cancellation, Refund & Return Tracking)
        // =========================================================================
        if (!Schema::hasTable('tax_adjustments')) {
            Schema::create('tax_adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->enum('adjustment_type', ['refund', 'cancellation', 'manual_correction', 'return_exchange']);
                $table->decimal('original_tax_amount', 12, 2)->default(0.00);
                $table->decimal('adjusted_tax_amount', 12, 2)->default(0.00);
                $table->decimal('net_tax_change', 12, 2)->default(0.00);
                $table->string('reference_no', 100)->nullable();
                $table->text('reason')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'adjustment_type']);
            });
        }

        // =========================================================================
        // ৬. TAX COMPLIANCE DOCUMENTS TABLE (BIN, Trade License, Mushak Records)
        // =========================================================================
        if (!Schema::hasTable('tax_compliance_documents')) {
            Schema::create('tax_compliance_documents', function (Blueprint $table) {
                $table->id();
                $table->string('doc_type', 50); // bin_certificate, trade_license, vat_challan, etc.
                $table->string('title', 150);
                $table->string('reference_no', 100)->nullable();
                $table->string('file_path', 255);
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->json('meta_data')->nullable(); // Additional regulatory metadata
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->timestamps();

                $table->index(['doc_type', 'reference_no']);
            });
        }

        // =========================================================================
        // ৭. ENHANCE ORDERS TABLE (Safe nullable tax columns & audit snapshot)
        // =========================================================================
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'tax_amount')) {
                    $table->decimal('tax_amount', 12, 2)->default(0.00);
                }
                if (!Schema::hasColumn('orders', 'product_tax_amount')) {
                    $table->decimal('product_tax_amount', 12, 2)->default(0.00);
                }
                if (!Schema::hasColumn('orders', 'shipping_tax_amount')) {
                    $table->decimal('shipping_tax_amount', 12, 2)->default(0.00);
                }
                if (!Schema::hasColumn('orders', 'service_tax_amount')) {
                    $table->decimal('service_tax_amount', 12, 2)->default(0.00);
                }
                if (!Schema::hasColumn('orders', 'is_tax_inclusive')) {
                    $table->boolean('is_tax_inclusive')->default(false);
                }
                if (!Schema::hasColumn('orders', 'tax_details_snapshot')) {
                    $table->json('tax_details_snapshot')->nullable();
                }
            });
        }

        // =========================================================================
        // ৮. ENHANCE ORDER ITEMS TABLE (Granular line-item tax calculation audit)
        // =========================================================================
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'tax_rule_id')) {
                    $table->unsignedBigInteger('tax_rule_id')->nullable();
                }
                if (!Schema::hasColumn('order_items', 'tax_class')) {
                    $table->string('tax_class', 50)->nullable();
                }
                if (!Schema::hasColumn('order_items', 'tax_rate')) {
                    $table->decimal('tax_rate', 8, 4)->default(0.0000);
                }
                if (!Schema::hasColumn('order_items', 'tax_amount')) {
                    $table->decimal('tax_amount', 12, 2)->default(0.00);
                }
                if (!Schema::hasColumn('order_items', 'is_inclusive')) {
                    $table->boolean('is_inclusive')->default(false);
                }
                if (!Schema::hasColumn('order_items', 'taxable_base_amount')) {
                    $table->decimal('taxable_base_amount', 12, 2)->default(0.00);
                }
            });
        }

        // =========================================================================
        // ৯. ENHANCE PRODUCTS TABLE (Safe appends without assumed column positions)
        // =========================================================================
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'tax_class_id')) {
                    $table->unsignedBigInteger('tax_class_id')->nullable();
                }
                if (!Schema::hasColumn('products', 'is_tax_exempt')) {
                    $table->boolean('is_tax_exempt')->default(false);
                }
                if (!Schema::hasColumn('products', 'override_tax_rate')) {
                    $table->decimal('override_tax_rate', 8, 4)->nullable();
                }
                if (!Schema::hasColumn('products', 'tax_type')) {
                    $table->enum('tax_type', ['inherit', 'inclusive', 'exclusive'])->default('inherit');
                }
            });
        }

        // =========================================================================
        // ১০. ENHANCE CATEGORIES TABLE (Safe appends without assumed column positions)
        // =========================================================================
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'tax_class_id')) {
                    $table->unsignedBigInteger('tax_class_id')->nullable();
                }
            });
        }

        // =========================================================================
        // ১১. ENHANCE BRANCHES TABLE (Safe appends without assumed column positions)
        // =========================================================================
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (!Schema::hasColumn('branches', 'branch_tax_rule_id')) {
                    $table->unsignedBigInteger('branch_tax_rule_id')->nullable();
                }
                if (!Schema::hasColumn('branches', 'bin_number')) {
                    $table->string('bin_number', 50)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                $cols = array_filter(['branch_tax_rule_id', 'bin_number'], fn($c) => Schema::hasColumn('branches', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'tax_class_id')) {
                    $table->dropColumn('tax_class_id');
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $cols = array_filter(['tax_class_id', 'is_tax_exempt', 'override_tax_rate', 'tax_type'], fn($c) => Schema::hasColumn('products', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $cols = array_filter(['tax_rule_id', 'tax_class', 'tax_rate', 'tax_amount', 'is_inclusive', 'taxable_base_amount'], fn($c) => Schema::hasColumn('order_items', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $cols = array_filter(['tax_amount', 'product_tax_amount', 'shipping_tax_amount', 'service_tax_amount', 'is_tax_inclusive', 'tax_details_snapshot'], fn($c) => Schema::hasColumn('orders', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('tax_compliance_documents');
        Schema::dropIfExists('tax_adjustments');
        Schema::dropIfExists('tax_payments');
        Schema::dropIfExists('tax_rule_conditions');
        Schema::dropIfExists('tax_rules');
        Schema::dropIfExists('tax_classes');
    }
};
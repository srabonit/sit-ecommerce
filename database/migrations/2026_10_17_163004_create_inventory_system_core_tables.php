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
        // ১. Suppliers Table
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('company_name')->nullable();
                $table->string('phone')->nullable()->index();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->decimal('opening_balance', 12, 2)->default(0.00);
                $table->string('status', 20)->default('active');
                $table->timestamps();
            });
        }

        // ২. Purchases (Inbound Shipments / PO Header) Table
        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_no', 60)->unique();
                $table->unsignedBigInteger('supplier_id')->nullable()->index();
                $table->string('supplier_invoice_no', 100)->nullable();
                $table->date('purchase_date')->index();
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->decimal('discount', 12, 2)->default(0.00);
                $table->decimal('shipping_cost', 12, 2)->default(0.00);
                $table->decimal('grand_total', 12, 2)->default(0.00);
                $table->decimal('paid_amount', 12, 2)->default(0.00);
                $table->decimal('due_amount', 12, 2)->default(0.00);
                $table->string('payment_status', 30)->default('unpaid'); // paid, partial, unpaid
                $table->string('status', 30)->default('received');       // received, pending, ordered
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        // ৩. Purchase Items (Inbound Product / Variant Breakdown) Table
        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('variant_id')->nullable()->index();
                $table->integer('quantity');
                $table->decimal('unit_cost', 12, 2)->default(0.00);
                $table->decimal('subtotal', 12, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            });
        }

        // ৪. Inventory Movements Table (Create if missing or safely add columns)
        if (!Schema::hasTable('inventory_movements')) {
            Schema::create('inventory_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('variant_id')->nullable()->index();
                $table->string('sku', 100)->nullable()->index();
                $table->string('movement_type', 50)->index();
                $table->integer('quantity_before')->default(0);
                $table->integer('quantity_change');
                $table->integer('quantity_after')->default(0);
                $table->decimal('unit_cost', 12, 2)->default(0.00);
                $table->decimal('unit_price', 12, 2)->default(0.00);
                $table->string('reference_id', 100)->nullable()->index();
                $table->string('source', 50)->default('system');
                $table->string('idempotency_token', 100)->nullable()->unique();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        } else {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_movements', 'idempotency_token')) {
                    $table->string('idempotency_token', 100)->nullable()->unique()->after('source');
                }
                if (!Schema::hasColumn('inventory_movements', 'unit_price')) {
                    $table->decimal('unit_price', 12, 2)->default(0.00)->after('unit_cost');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
        
        if (Schema::hasTable('inventory_movements') && Schema::hasColumn('inventory_movements', 'idempotency_token')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->dropColumn(['idempotency_token', 'unit_price']);
            });
        }
    }
};
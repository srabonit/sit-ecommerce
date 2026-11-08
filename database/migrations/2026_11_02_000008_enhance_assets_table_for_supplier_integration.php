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
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                // Supplier & Purchase Relationships
                if (!Schema::hasColumn('assets', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                }
                if (!Schema::hasColumn('assets', 'purchase_id')) {
                    $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
                }

                // Hardware & Asset Identification Specifications
                if (!Schema::hasColumn('assets', 'serial_number')) {
                    $table->string('serial_number', 150)->nullable();
                }
                if (!Schema::hasColumn('assets', 'model_number')) {
                    $table->string('model_number', 100)->nullable();
                }
                if (!Schema::hasColumn('assets', 'brand_name')) {
                    $table->string('brand_name', 100)->nullable();
                }

                // Department & Location Assignment
                if (!Schema::hasColumn('assets', 'department')) {
                    $table->string('department', 100)->nullable();
                }
                if (!Schema::hasColumn('assets', 'physical_location')) {
                    $table->string('physical_location', 200)->nullable();
                }
                if (!Schema::hasColumn('assets', 'assigned_user_id')) {
                    $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                }

                // Condition, Status & Lifecycle Tracking
                if (!Schema::hasColumn('assets', 'condition')) {
                    $table->enum('condition', [
                        'brand_new',
                        'good',
                        'fair',
                        'under_repair',
                        'damaged'
                    ])->default('brand_new');
                }
                if (!Schema::hasColumn('assets', 'status')) {
                    $table->enum('status', [
                        'in_use',
                        'in_stock',
                        'under_maintenance',
                        'returned_to_supplier',
                        'disposed',
                        'written_off'
                    ])->default('in_use');
                }

                // Valuation & Depreciation Settings
                if (!Schema::hasColumn('assets', 'current_value')) {
                    $table->decimal('current_value', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('assets', 'salvage_value')) {
                    $table->decimal('salvage_value', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('assets', 'depreciation_method')) {
                    $table->enum('depreciation_method', [
                        'none',
                        'straight_line',
                        'declining_balance'
                    ])->default('none');
                }
                if (!Schema::hasColumn('assets', 'depreciation_rate')) {
                    $table->decimal('depreciation_rate', 5, 2)->default(0.00);
                }
                if (!Schema::hasColumn('assets', 'accumulated_depreciation')) {
                    $table->decimal('accumulated_depreciation', 14, 2)->default(0.00);
                }

                // Additional Document / Warranty Attachment
                if (!Schema::hasColumn('assets', 'warranty_document')) {
                    $table->string('warranty_document')->nullable();
                }

                // Performance Indexes
                $table->index(['supplier_id', 'status'], 'assets_supplier_status_idx');
                $table->index('serial_number', 'assets_serial_number_idx');
                $table->index('asset_code', 'assets_code_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback
    }
};
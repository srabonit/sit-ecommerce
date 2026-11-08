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
        // 1. Departments Table
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('code', 50)->nullable();
                $table->timestamps();
            });
        }

        // 2. Fixed Assets Table
        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->id();
                $table->string('asset_code', 100)->unique();
                $table->string('name', 255);
                $table->unsignedBigInteger('asset_category_id')->nullable()->index();
                $table->string('brand', 150)->nullable();
                $table->string('model_no', 150)->nullable();
                $table->string('serial_number', 150)->nullable();
                
                $table->unsignedBigInteger('supplier_id')->nullable()->index();
                $table->unsignedBigInteger('purchase_id')->nullable()->index();
                
                $table->date('purchase_date');
                $table->date('in_service_date')->nullable();
                
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('department_id')->nullable()->index();
                $table->unsignedBigInteger('custodian_id')->nullable()->index();
                $table->string('location', 255)->nullable();
                
                $table->decimal('purchase_cost', 14, 2)->default(0.00);
                $table->decimal('installation_cost', 14, 2)->default(0.00);
                $table->decimal('shipping_cost', 14, 2)->default(0.00);
                $table->decimal('salvage_value', 14, 2)->default(0.00);
                $table->decimal('capitalized_cost', 14, 2)->default(0.00);
                
                $table->string('depreciation_method', 50)->default('straight_line');
                $table->decimal('useful_life_years', 5, 2)->default(5.00);
                $table->decimal('depreciation_rate', 5, 2)->default(20.00);
                $table->decimal('accumulated_depreciation', 14, 2)->default(0.00);
                
                $table->unsignedBigInteger('asset_account_id')->nullable();
                $table->unsignedBigInteger('accumulated_depreciation_account_id')->nullable();
                $table->unsignedBigInteger('depreciation_expense_account_id')->nullable();
                
                $table->date('warranty_expiry_date')->nullable();
                $table->string('insurance_policy_no', 150)->nullable();
                $table->string('attachment_file', 255)->nullable();
                
                $table->string('status', 50)->default('active')->index();
                $table->string('disposal_type', 50)->nullable();
                $table->decimal('disposal_value', 14, 2)->default(0.00);
                $table->date('disposal_date')->nullable();
                
                $table->text('notes')->nullable();
                $table->timestamps();

                if (Schema::hasTable('suppliers')) {
                    $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
                }
                if (Schema::hasTable('purchases')) {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
        Schema::dropIfExists('departments');
    }
};
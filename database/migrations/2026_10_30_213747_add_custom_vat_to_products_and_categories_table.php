<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Products table-e optional VAT fields
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'vat_type')) {
                    $table->string('vat_type', 20)->nullable()->default('percent')->comment('percent ba flat')->after('regular_price');
                }
                if (!Schema::hasColumn('products', 'vat_amount')) {
                    $table->decimal('vat_amount', 10, 2)->nullable()->default(null)->comment('Khali thakle 0, noyto % ba flat amount')->after('vat_type');
                }
            });
        }

        // 2. Categories table-e optional VAT fields
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'vat_type')) {
                    $table->string('vat_type', 20)->nullable()->default('percent')->comment('percent ba flat')->after('name');
                }
                if (!Schema::hasColumn('categories', 'vat_amount')) {
                    $table->decimal('vat_amount', 10, 2)->nullable()->default(null)->comment('Category-level override VAT')->after('vat_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['vat_type', 'vat_amount']);
            });
        }
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn(['vat_type', 'vat_amount']);
            });
        }
    }
};
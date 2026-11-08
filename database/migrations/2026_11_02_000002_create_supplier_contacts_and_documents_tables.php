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
        // 1. Multiple Supplier Contacts Table
        if (!Schema::hasTable('supplier_contacts')) {
            Schema::create('supplier_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->string('name');
                $table->string('designation')->nullable();
                $table->string('phone');
                $table->string('alternate_phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'is_primary']);
                $table->index('phone');
            });
        }

        // 2. Supplier Compliance Documents Table (Trade License, Agreement, Tax Certificate etc.)
        if (!Schema::hasTable('supplier_documents')) {
            Schema::create('supplier_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->string('document_type')->default('other'); // trade_license, agreement, tin_certificate, bank_cheque, other
                $table->string('title');
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size_kb')->nullable();
                $table->string('file_extension', 20)->nullable();
                $table->date('expiry_date')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'document_type']);
                $table->index('expiry_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_documents');
        Schema::dropIfExists('supplier_contacts');
    }
};
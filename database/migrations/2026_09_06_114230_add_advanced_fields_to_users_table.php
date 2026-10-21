<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'joining_date')) $table->date('joining_date')->nullable()->after('dob');
            if (!Schema::hasColumn('users', 'resignation_date')) $table->date('resignation_date')->nullable()->after('joining_date');
            if (!Schema::hasColumn('users', 'reference_name')) $table->string('reference_name')->nullable()->after('resignation_date');
            if (!Schema::hasColumn('users', 'reference_phone')) $table->string('reference_phone')->nullable()->after('reference_name');
            if (!Schema::hasColumn('users', 'created_by')) $table->unsignedBigInteger('created_by')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['joining_date', 'resignation_date', 'reference_name', 'reference_phone', 'created_by']);
        });
    }
};
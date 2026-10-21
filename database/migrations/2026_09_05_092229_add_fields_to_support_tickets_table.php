<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('support_tickets', 'user_email')) {
                $table->string('user_email')->nullable()->after('user_name');
            }
            if (!Schema::hasColumn('support_tickets', 'case_type')) {
                $table->string('case_type')->nullable()->after('ticket_no');
            }
            if (!Schema::hasColumn('support_tickets', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->after('case_type');
            }
            if (!Schema::hasColumn('support_tickets', 'message')) {
                $table->text('message')->nullable()->after('subject');
            }
        });
    }

    public function down(): void
    {
        // Safe rollback
    }
};
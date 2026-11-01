<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('form_submissions', 'replied_by')) {
                $table->unsignedBigInteger('replied_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('form_submissions', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('replied_by');
            }
            if (!Schema::hasColumn('form_submissions', 'reply_subject')) {
                $table->string('reply_subject', 255)->nullable()->after('replied_at');
            }
            if (!Schema::hasColumn('form_submissions', 'reply_message')) {
                $table->longText('reply_message')->nullable()->after('reply_subject');
            }
            if (!Schema::hasColumn('form_submissions', 'is_ip_blocked')) {
                $table->boolean('is_ip_blocked')->default(false)->after('reply_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn(['replied_by', 'replied_at', 'reply_subject', 'reply_message', 'is_ip_blocked']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('media_files')) {
            Schema::table('media_files', function (Blueprint $table) {
                if (!Schema::hasColumn('media_files', 'file_hash')) {
                    $table->string('file_hash', 64)->nullable()->after('file_size')->index();
                }
                if (!Schema::hasColumn('media_files', 'uploaded_by')) {
                    $table->unsignedBigInteger('uploaded_by')->nullable()->after('file_hash')->index();
                }
                if (!Schema::hasColumn('media_files', 'disk')) {
                    $table->string('disk', 30)->default('public')->after('file_path');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('media_files')) {
            Schema::table('media_files', function (Blueprint $table) {
                $table->dropColumn(['file_hash', 'uploaded_by', 'disk']);
            });
        }
    }
};
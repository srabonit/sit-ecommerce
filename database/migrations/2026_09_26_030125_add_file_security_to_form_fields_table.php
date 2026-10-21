<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('form_fields')) {
            Schema::table('form_fields', function (Blueprint $table) {
                if (!Schema::hasColumn('form_fields', 'file_max_size')) {
                    $table->unsignedInteger('file_max_size')->nullable();
                }

                if (!Schema::hasColumn('form_fields', 'file_max_files')) {
                    $table->unsignedInteger('file_max_files')->default(1);
                }

                if (!Schema::hasColumn('form_fields', 'file_allowed_mimes')) {
                    $table->text('file_allowed_mimes')->nullable();
                }

                if (!Schema::hasColumn('form_fields', 'file_allowed_extensions')) {
                    $table->text('file_allowed_extensions')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('form_fields')) {
            Schema::table('form_fields', function (Blueprint $table) {
                $columnsToDrop = [];

                if (Schema::hasColumn('form_fields', 'file_max_size')) {
                    $columnsToDrop[] = 'file_max_size';
                }
                if (Schema::hasColumn('form_fields', 'file_max_files')) {
                    $columnsToDrop[] = 'file_max_files';
                }
                if (Schema::hasColumn('form_fields', 'file_allowed_mimes')) {
                    $columnsToDrop[] = 'file_allowed_mimes';
                }
                if (Schema::hasColumn('form_fields', 'file_allowed_extensions')) {
                    $columnsToDrop[] = 'file_allowed_extensions';
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
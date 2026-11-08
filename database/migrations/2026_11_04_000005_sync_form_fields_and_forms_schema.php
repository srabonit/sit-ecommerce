<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sync Forms Table with Form Model Attributes
        if (Schema::hasTable('forms')) {
            Schema::table('forms', function (Blueprint $table) {
                if (!Schema::hasColumn('forms', 'name')) $table->string('name')->after('id');
                if (!Schema::hasColumn('forms', 'slug')) $table->string('slug')->unique()->after('name');
                if (!Schema::hasColumn('forms', 'show_title')) $table->boolean('show_title')->default(true);
                if (!Schema::hasColumn('forms', 'description')) $table->text('description')->nullable();
                if (!Schema::hasColumn('forms', 'show_description')) $table->boolean('show_description')->default(true);
                if (!Schema::hasColumn('forms', 'logo')) $table->string('logo', 1000)->nullable();
                if (!Schema::hasColumn('forms', 'styles')) $table->longText('styles')->nullable();
                if (!Schema::hasColumn('forms', 'theme_preset')) $table->string('theme_preset', 100)->nullable();
                if (!Schema::hasColumn('forms', 'submit_button_text')) $table->string('submit_button_text')->nullable();
                if (!Schema::hasColumn('forms', 'submit_button_class')) $table->string('submit_button_class')->nullable();
                if (!Schema::hasColumn('forms', 'custom_css')) $table->longText('custom_css')->nullable();
                if (!Schema::hasColumn('forms', 'admin_email')) $table->string('admin_email')->nullable();
                if (!Schema::hasColumn('forms', 'email_subject')) $table->string('email_subject')->nullable();
                if (!Schema::hasColumn('forms', 'send_admin_email')) $table->boolean('send_admin_email')->default(true);
                if (!Schema::hasColumn('forms', 'user_email_subject')) $table->string('user_email_subject')->nullable();
                if (!Schema::hasColumn('forms', 'send_user_email')) $table->boolean('send_user_email')->default(false);
                if (!Schema::hasColumn('forms', 'success_message')) $table->text('success_message')->nullable();
                if (!Schema::hasColumn('forms', 'redirect_url')) $table->string('redirect_url', 500)->nullable();
                if (!Schema::hasColumn('forms', 'is_active')) $table->boolean('is_active')->default(true);
                if (!Schema::hasColumn('forms', 'save_submissions')) $table->boolean('save_submissions')->default(true);
                if (!Schema::hasColumn('forms', 'captcha_type')) $table->string('captcha_type', 50)->nullable();
                if (!Schema::hasColumn('forms', 'rate_limit_per_ip')) $table->integer('rate_limit_per_ip')->default(15);
                if (!Schema::hasColumn('forms', 'prevent_duplicates')) $table->boolean('prevent_duplicates')->default(false);
                if (!Schema::hasColumn('forms', 'webhook_url')) $table->string('webhook_url', 1000)->nullable();
                if (!Schema::hasColumn('forms', 'webhook_method')) $table->string('webhook_method', 10)->nullable();
                if (!Schema::hasColumn('forms', 'webhook_auth_header')) $table->string('webhook_auth_header', 500)->nullable();
            });
        }

        // 2. Sync Form Fields Table with FormField Model Attributes
        if (Schema::hasTable('form_fields')) {
            Schema::table('form_fields', function (Blueprint $table) {
                if (!Schema::hasColumn('form_fields', 'form_id')) {
                    $table->unsignedBigInteger('form_id')->after('id')->index();
                }
                if (!Schema::hasColumn('form_fields', 'type')) {
                    $table->string('type', 50)->default('text')->after('form_id');
                }
                if (!Schema::hasColumn('form_fields', 'label')) {
                    $table->string('label')->nullable()->after('type');
                }
                if (!Schema::hasColumn('form_fields', 'name')) {
                    $table->string('name')->nullable()->after('label');
                }
                if (!Schema::hasColumn('form_fields', 'placeholder')) {
                    $table->string('placeholder')->nullable()->after('name');
                }
                if (!Schema::hasColumn('form_fields', 'default_value')) {
                    $table->text('default_value')->nullable()->after('placeholder');
                }
                if (!Schema::hasColumn('form_fields', 'help_text')) {
                    $table->text('help_text')->nullable()->after('default_value');
                }
                if (!Schema::hasColumn('form_fields', 'options')) {
                    $table->longText('options')->nullable()->after('help_text');
                }
                if (!Schema::hasColumn('form_fields', 'css_class')) {
                    $table->string('css_class')->nullable()->after('options');
                }
                if (!Schema::hasColumn('form_fields', 'wrapper_class')) {
                    $table->string('wrapper_class')->nullable()->after('css_class');
                }
                if (!Schema::hasColumn('form_fields', 'column_width')) {
                    $table->integer('column_width')->default(12)->after('wrapper_class');
                }
                if (!Schema::hasColumn('form_fields', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('column_width')->index();
                }
                if (!Schema::hasColumn('form_fields', 'is_required')) {
                    $table->boolean('is_required')->default(false)->after('sort_order');
                }
                if (!Schema::hasColumn('form_fields', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_required');
                }
                if (!Schema::hasColumn('form_fields', 'min_length')) {
                    $table->integer('min_length')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('form_fields', 'max_length')) {
                    $table->integer('max_length')->nullable()->after('min_length');
                }
                if (!Schema::hasColumn('form_fields', 'min_value')) {
                    $table->decimal('min_value', 12, 2)->nullable()->after('max_length');
                }
                if (!Schema::hasColumn('form_fields', 'max_value')) {
                    $table->decimal('max_value', 12, 2)->nullable()->after('min_value');
                }
                if (!Schema::hasColumn('form_fields', 'file_max_size')) {
                    $table->integer('file_max_size')->nullable()->after('max_value');
                }
                if (!Schema::hasColumn('form_fields', 'file_max_files')) {
                    $table->integer('file_max_files')->default(1)->after('file_max_size');
                }
                if (!Schema::hasColumn('form_fields', 'file_allowed_mimes')) {
                    $table->text('file_allowed_mimes')->nullable()->after('file_max_files');
                }
                if (!Schema::hasColumn('form_fields', 'file_allowed_extensions')) {
                    $table->text('file_allowed_extensions')->nullable()->after('file_allowed_mimes');
                }
            });

            // Safe Foreign Key: Only add if the constraint doesn't already exist
            if (Schema::hasTable('forms')) {
                $existingConstraint = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE CONSTRAINT_SCHEMA = DATABASE() 
                      AND TABLE_NAME = 'form_fields' 
                      AND CONSTRAINT_NAME = 'form_fields_form_id_foreign'
                ");

                if (empty($existingConstraint)) {
                    Schema::table('form_fields', function (Blueprint $table) {
                        $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
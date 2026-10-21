<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ১. users টেবিলে স্টাফ প্রোফাইল, নিরাপত্তা ও লকআউট কলাম যুক্ত করা
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'designation')) $table->string('designation')->nullable()->after('name');
            if (!Schema::hasColumn('users', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable()->after('role');
            if (!Schema::hasColumn('users', 'phone_alt')) $table->string('phone_alt')->nullable()->after('phone');
            if (!Schema::hasColumn('users', 'email_alt')) $table->string('email_alt')->nullable()->after('email');
            if (!Schema::hasColumn('users', 'nid_number')) $table->string('nid_number')->nullable()->after('phone_alt');
            if (!Schema::hasColumn('users', 'dob')) $table->date('dob')->nullable()->after('nid_number');
            if (!Schema::hasColumn('users', 'photo')) $table->string('photo')->nullable()->after('dob');
            if (!Schema::hasColumn('users', 'bio')) $table->text('bio')->nullable()->after('photo');
            if (!Schema::hasColumn('users', 'note')) $table->text('note')->nullable()->after('bio');
            
            // বেতন ও অ্যাটেন্ডেন্স রেট
            if (!Schema::hasColumn('users', 'monthly_salary')) $table->decimal('monthly_salary', 12, 2)->default(0)->after('note');
            if (!Schema::hasColumn('users', 'daily_rate')) $table->decimal('daily_rate', 10, 2)->default(0)->after('monthly_salary');
            if (!Schema::hasColumn('users', 'salary_pay_day')) $table->tinyInteger('salary_pay_day')->default(5)->after('daily_rate'); // প্রতি মাসের কত তারিখে প্রদেয়

            // ২-স্টেপ ইমেইল ওটিপি ও সুরক্ষা
            if (!Schema::hasColumn('users', 'otp_code')) $table->string('otp_code', 10)->nullable()->after('password');
            if (!Schema::hasColumn('users', 'otp_expires_at')) $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            if (!Schema::hasColumn('users', 'last_activity_at')) $table->timestamp('last_activity_at')->nullable()->after('otp_expires_at');
            
            // লকআউট ও অ্যাটেম্পট ট্র্যাকিং
            if (!Schema::hasColumn('users', 'password_attempts')) $table->tinyInteger('password_attempts')->default(0)->after('last_activity_at');
            if (!Schema::hasColumn('users', 'otp_attempts')) $table->tinyInteger('otp_attempts')->default(0)->after('password_attempts');
            if (!Schema::hasColumn('users', 'is_locked')) $table->boolean('is_locked')->default(false)->after('otp_attempts');
            if (!Schema::hasColumn('users', 'locked_reason')) $table->string('locked_reason')->nullable()->after('is_locked');
            if (!Schema::hasColumn('users', 'is_super_admin')) $table->boolean('is_super_admin')->default(false)->after('is_locked');
            if (!Schema::hasColumn('users', 'deleted_at')) $table->softDeletes();
        });

        // ২. স্টাফ ও এডমিনদের মাল্টিপল ডকুমেন্টস আপলোড টেবিল (NID, Certificate, CV, Contracts)
        if (!Schema::hasTable('user_documents')) {
            Schema::create('user_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('doc_title'); // যেমন: National ID Card, Joining Letter
                $table->string('file_path');
                $table->string('file_type')->nullable(); // pdf, jpg, png
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // ৩. ডাইনামিক রোল ওয়াইজ রাউট পারমিশন টেবিল (যদি permission_tables এ ম্যাট্রিক্স না থাকে)
        if (!Schema::hasTable('role_route_permissions')) {
            Schema::create('role_route_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('role_name');
                $table->string('route_module'); // user, order, product, etc.
                $table->boolean('can_create')->default(false);
                $table->boolean('can_update')->default(false);
                $table->boolean('can_delete')->default(false);
                $table->boolean('can_read')->default(false);
                $table->timestamps();
                $table->unique(['role_name', 'route_module']);
            });
        }

        // ৪. স্টাফ ও এডমিনদের দৈনিক উপস্থিতি (Attendance)
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->date('attendance_date');
                $table->time('clock_in')->nullable();
                $table->time('clock_out')->nullable();
                $table->enum('status', ['present', 'absent', 'late', 'half_day', 'holiday', 'leave'])->default('present');
                $table->text('admin_remarks')->nullable();
                $table->unsignedBigInteger('marked_by')->nullable(); // সুপার এডমিন আইডি
                $table->timestamps();
                $table->unique(['user_id', 'attendance_date']);
            });
        }

        // ৫. পেরোল ও বেতন হিসাব (Monthly, Advance, Paid, Partial Paid)
        if (!Schema::hasTable('staff_payrolls')) {
            Schema::create('staff_payrolls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('month_year', 20); //  September-2026
                $table->decimal('basic_salary', 12, 2)->default(0);
                $table->integer('total_days')->default(30);
                $table->integer('present_days')->default(0);
                $table->integer('absent_days')->default(0);
                $table->decimal('advance_deduction', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->decimal('net_payable', 12, 2)->default(0);
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->decimal('due_amount', 12, 2)->default(0);
                $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'advance'])->default('unpaid');
                $table->date('payment_date')->nullable();
                $table->string('payment_method')->nullable(); // Cash, Bank, Bkash
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        // ৬. অগ্রিম বেতন ট্র্যাকিং (Advance Salary)
        if (!Schema::hasTable('advance_salaries')) {
            Schema::create('advance_salaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->decimal('advance_amount', 12, 2);
                $table->date('request_date');
                $table->string('deduct_month', 20); // কোন মাসের বেতন থেকে কাটা হবে
                $table->enum('status', ['pending', 'approved', 'deducted', 'rejected'])->default('pending');
                $table->text('purpose')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_salaries');
        Schema::dropIfExists('staff_payrolls');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('role_route_permissions');
        Schema::dropIfExists('user_documents');
    }
};
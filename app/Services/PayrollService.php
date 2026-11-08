<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PayrollService
{
    /**
     * Ensure staff_payrolls, bank_accounts and users payroll columns exist safely without migration failure.
     */
    public static function ensurePayrollTable(): void
    {
        // 1. Ensure users table has required salary and joining columns
        if (Schema::hasTable('users')) {
            Schema::table('users', function ($table) {
                if (!Schema::hasColumn('users', 'salary')) {
                    $table->decimal('salary', 12, 2)->default(0.00)->nullable();
                }
                if (!Schema::hasColumn('users', 'joining_date')) {
                    $table->date('joining_date')->nullable();
                }
                if (!Schema::hasColumn('users', 'salary_type')) {
                    $table->string('salary_type', 30)->default('monthly')->nullable();
                }
            });
        }

        // 2. Ensure staff_payrolls table exists
        if (!Schema::hasTable('staff_payrolls')) {
            Schema::create('staff_payrolls', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->integer('salary_year');
                $table->integer('salary_month');
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->integer('working_days')->default(30);
                $table->integer('worked_days')->default(30);
                $table->decimal('basic_salary', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->decimal('deduction', 12, 2)->default(0);
                $table->decimal('net_salary', 12, 2)->default(0);
                $table->string('status', 30)->default('pending');
                $table->string('payment_method')->nullable();
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->unsignedBigInteger('expense_id')->nullable();
                $table->string('voucher_no')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('paid_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'salary_year', 'salary_month']);
            });
        } else {
            Schema::table('staff_payrolls', function ($table) {
                if (!Schema::hasColumn('staff_payrolls', 'salary_year')) {
                    $table->integer('salary_year')->default(date('Y'));
                }
                if (!Schema::hasColumn('staff_payrolls', 'salary_month')) {
                    $table->integer('salary_month')->default(date('m'));
                }
                if (!Schema::hasColumn('staff_payrolls', 'period_start')) {
                    $table->date('period_start')->nullable();
                }
                if (!Schema::hasColumn('staff_payrolls', 'period_end')) {
                    $table->date('period_end')->nullable();
                }
                if (!Schema::hasColumn('staff_payrolls', 'working_days')) {
                    $table->integer('working_days')->default(30);
                }
                if (!Schema::hasColumn('staff_payrolls', 'worked_days')) {
                    $table->integer('worked_days')->default(30);
                }
                if (!Schema::hasColumn('staff_payrolls', 'basic_salary')) {
                    $table->decimal('basic_salary', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('staff_payrolls', 'bonus')) {
                    $table->decimal('bonus', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('staff_payrolls', 'deduction')) {
                    $table->decimal('deduction', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('staff_payrolls', 'net_salary')) {
                    $table->decimal('net_salary', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('staff_payrolls', 'status')) {
                    $table->string('status', 30)->default('pending');
                }
                if (!Schema::hasColumn('staff_payrolls', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                }
                if (!Schema::hasColumn('staff_payrolls', 'bank_account_id')) {
                    $table->unsignedBigInteger('bank_account_id')->nullable();
                }
                if (!Schema::hasColumn('staff_payrolls', 'expense_id')) {
                    $table->unsignedBigInteger('expense_id')->nullable();
                }
                if (!Schema::hasColumn('staff_payrolls', 'voucher_no')) {
                    $table->string('voucher_no')->nullable();
                }
                if (!Schema::hasColumn('staff_payrolls', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable();
                }
                if (!Schema::hasColumn('staff_payrolls', 'paid_by')) {
                    $table->unsignedBigInteger('paid_by')->nullable();
                }
                if (!Schema::hasColumn('staff_payrolls', 'notes')) {
                    $table->text('notes')->nullable();
                }
            });
        }

        // 3. Ensure bank_accounts table exists in database
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function ($table) {
                $table->id();
                $table->string('bank_name')->default('Cash in Hand');
                $table->string('account_name')->default('Main Cash Counter');
                $table->string('account_number')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('account_type')->default('cash');
                $table->decimal('opening_balance', 14, 2)->default(0.00);
                $table->decimal('current_balance', 14, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::table('bank_accounts')->insert([
                'bank_name'       => 'Cash in Hand',
                'account_name'    => 'Main Cash Counter',
                'account_number'  => 'CASH-01',
                'account_type'    => 'cash',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    /**
     * Calculate monthly salary for a specific staff member.
     */
    public static function calculateStaffSalaryForMonth(object $staff, int $year, int $month): array
    {
        $basicSalary = (float) ($staff->salary ?? 0.00);
        if ($basicSalary <= 0) {
            return [
                'eligible'     => false,
                'reason'       => 'Salary is not defined for this user',
                'worked_days'  => 0,
                'total_days'   => 30,
                'daily_rate'   => 0.00,
                'basic_salary' => 0.00,
                'net_payable'  => 0.00,
            ];
        }

        $cycleStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $cycleCutoff = Carbon::createFromDate($year, $month, 30)->endOfDay();

        $joiningDateRaw = $staff->joining_date ?? ($staff->appointment_date ?? ($staff->created_at ?? null));
        $joiningCarbon = $joiningDateRaw ? Carbon::parse($joiningDateRaw)->startOfDay() : $cycleStart;

        if ($joiningCarbon->gt($cycleCutoff)) {
            return [
                'eligible'     => false,
                'reason'       => 'Staff joined after the cycle cutoff date',
                'worked_days'  => 0,
                'total_days'   => 30,
                'daily_rate'   => round($basicSalary / 30, 2),
                'basic_salary' => $basicSalary,
                'net_payable'  => 0.00,
            ];
        }

        $dailyRate = round($basicSalary / 30, 2);

        if ($joiningCarbon->year === $year && $joiningCarbon->month === $month) {
            $joinDay = min(30, (int) $joiningCarbon->day);
            $workedDays = max(1, 30 - $joinDay + 1);
            $netPayable = round($workedDays * $dailyRate, 2);
        } else {
            $workedDays = 30;
            $netPayable = $basicSalary;
        }

        return [
            'eligible'     => true,
            'worked_days'  => $workedDays,
            'total_days'   => 30,
            'daily_rate'   => $dailyRate,
            'basic_salary' => $basicSalary,
            'net_payable'  => $netPayable,
            'period_start' => $joiningCarbon->gt($cycleStart) ? $joiningCarbon->toDateString() : $cycleStart->toDateString(),
            'period_end'   => $cycleCutoff->toDateString(),
        ];
    }

    /**
     * Generate or sync monthly salary sheet for all eligible staff.
     */
    public static function generateMonthlyPayroll(int $year, int $month, ?int $adminId = null): array
    {
        self::ensurePayrollTable();

        $hasSalaryCol = Schema::hasColumn('users', 'salary');

        $staffUsers = DB::table('users')
            ->where(function ($q) use ($hasSalaryCol) {
                $q->whereIn('role', ['staff', 'employee', 'manager', 'admin']);
                if ($hasSalaryCol) {
                    $q->orWhere('salary', '>', 0);
                }
            })
            ->whereNull('deleted_at')
            ->get();

        $generated = [];

        foreach ($staffUsers as $staff) {
            $calc = self::calculateStaffSalaryForMonth($staff, $year, $month);
            if (!$calc['eligible']) {
                continue;
            }

            $existing = DB::table('staff_payrolls')
                ->where('user_id', $staff->id)
                ->where('salary_year', $year)
                ->where('salary_month', $month)
                ->first();

            if (!$existing) {
                $voucherNo = 'SAL-' . $year . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . '-' . $staff->id;
                $payrollId = DB::table('staff_payrolls')->insertGetId([
                    'user_id'        => $staff->id,
                    'salary_year'    => $year,
                    'salary_month'   => $month,
                    'period_start'   => $calc['period_start'],
                    'period_end'     => $calc['period_end'],
                    'working_days'   => 30,
                    'worked_days'    => $calc['worked_days'],
                    'basic_salary'   => $calc['basic_salary'],
                    'bonus'          => 0.00,
                    'deduction'      => 0.00,
                    'net_salary'     => $calc['net_payable'],
                    'status'         => 'pending',
                    'voucher_no'     => $voucherNo,
                    'paid_by'        => $adminId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                $generated[] = [
                    'payroll_id'   => $payrollId,
                    'staff_name'   => $staff->name,
                    'worked_days'  => $calc['worked_days'],
                    'net_payable'  => $calc['net_payable'],
                    'status'       => 'generated',
                ];
            } else {
                $generated[] = [
                    'payroll_id'   => $existing->id,
                    'staff_name'   => $staff->name,
                    'worked_days'  => $existing->worked_days ?? 30,
                    'net_payable'  => (float) $existing->net_salary,
                    'status'       => $existing->status ?? 'pending',
                ];
            }
        }

        return $generated;
    }

    /**
     * Atomically disburse salary to a staff member.
     */
    public static function disburseSalary(
        int $payrollId,
        int $bankAccountId,
        string $paymentMethod = 'bank_transfer',
        ?string $note = null,
        ?int $adminId = null
    ): array {
        self::ensurePayrollTable();

        return DB::transaction(function () use ($payrollId, $bankAccountId, $paymentMethod, $note, $adminId) {
            $payroll = DB::table('staff_payrolls')
                ->where('id', $payrollId)
                ->lockForUpdate()
                ->first();

            if (!$payroll) {
                throw new Exception("Payroll record #{$payrollId} not found.");
            }

            if (($payroll->status ?? '') === 'paid') {
                throw new Exception("Salary for Voucher #{$payroll->voucher_no} has already been paid.");
            }

            $payableAmount = (float) $payroll->net_salary;
            if ($payableAmount <= 0) {
                throw new Exception("Payable salary amount must be greater than zero.");
            }

            $bank = null;
            $currentBalance = 0.00;
            $bankName = 'Cash/Bank Account';

            if (Schema::hasTable('bank_accounts') && $bankAccountId > 0) {
                $bank = DB::table('bank_accounts')
                    ->where('id', $bankAccountId)
                    ->lockForUpdate()
                    ->first();

                if ($bank) {
                    $currentBalance = (float) ($bank->current_balance ?? 0.00);
                    $bankName = $bank->bank_name ?? ($bank->account_name ?? 'Bank Account');
                    if ($currentBalance < $payableAmount) {
                        throw new Exception("Insufficient bank balance. Available: ৳" . number_format($currentBalance, 2) . ", Required: ৳" . number_format($payableAmount, 2));
                    }

                    DB::table('bank_accounts')
                        ->where('id', $bankAccountId)
                        ->update([
                            'current_balance' => round($currentBalance - $payableAmount, 2),
                            'updated_at'      => now(),
                        ]);
                }
            }

            // Resolve or create "Staff Salary" expense category
            $salaryCatId = null;
            if (Schema::hasTable('expense_categories')) {
                $salaryCat = DB::table('expense_categories')
                    ->where(function ($q) {
                        $q->where('name', 'LIKE', '%Salary%')
                          ->orWhere('name', 'LIKE', '%Payroll%')
                          ->orWhere('name', 'LIKE', '%বেতন%');
                    })
                    ->first();

                if ($salaryCat) {
                    $salaryCatId = $salaryCat->id;
                } else {
                    $salaryCatId = DB::table('expense_categories')->insertGetId([
                        'name'       => 'Staff Salary',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Create automated Expense entry
            $expenseId = null;
            if (Schema::hasTable('expenses')) {
                $staff = DB::table('users')->where('id', $payroll->user_id)->first();
                $monthName = Carbon::createFromDate($payroll->salary_year, $payroll->salary_month, 1)->format('F Y');
                $expenseTitle = "Staff Salary - " . ($staff->name ?? "Staff #{$payroll->user_id}") . " ({$monthName})";

                $expenseData = [
                    'amount'       => $payableAmount,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                if (Schema::hasColumn('expenses', 'category_id')) $expenseData['category_id'] = $salaryCatId;
                if (Schema::hasColumn('expenses', 'title')) $expenseData['title'] = $expenseTitle;
                if (Schema::hasColumn('expenses', 'name')) $expenseData['name'] = $expenseTitle;
                if (Schema::hasColumn('expenses', 'expense_date')) $expenseData['expense_date'] = now()->toDateString();
                if (Schema::hasColumn('expenses', 'bank_account_id')) $expenseData['bank_account_id'] = $bankAccountId;
                if (Schema::hasColumn('expenses', 'payment_method')) $expenseData['payment_method'] = $paymentMethod;
                if (Schema::hasColumn('expenses', 'reference_no')) $expenseData['reference_no'] = $payroll->voucher_no;
                if (Schema::hasColumn('expenses', 'note')) $expenseData['note'] = $note ?? "Disbursed monthly salary for {$monthName}";
                if (Schema::hasColumn('expenses', 'created_by')) $expenseData['created_by'] = $adminId ?? auth()->id();

                $expenseId = DB::table('expenses')->insertGetId($expenseData);
            }

            // Update Payroll Record
            DB::table('staff_payrolls')
                ->where('id', $payrollId)
                ->update([
                    'status'          => 'paid',
                    'payment_method'  => $paymentMethod,
                    'bank_account_id' => $bankAccountId,
                    'expense_id'      => $expenseId,
                    'paid_at'         => now(),
                    'paid_by'         => $adminId ?? auth()->id(),
                    'notes'           => $note,
                    'updated_at'      => now(),
                ]);

            return [
                'success'        => true,
                'payroll_id'     => $payrollId,
                'voucher_no'     => $payroll->voucher_no,
                'amount_paid'    => $payableAmount,
                'bank_name'      => $bankName,
                'remaining_bank' => round(max(0, $currentBalance - $payableAmount), 2),
                'expense_id'     => $expenseId,
            ];
        });
    }

    /**
     * Get staff salary history.
     */
    public static function getStaffSalaryHistory(int $userId): array
    {
        self::ensurePayrollTable();

        $staff = DB::table('users')->where('id', $userId)->first();
        if (!$staff) {
            return ['staff' => null, 'history' => collect(), 'total_paid' => 0.00, 'total_pending' => 0.00];
        }

        $hasBank = Schema::hasTable('bank_accounts');

        $query = DB::table('staff_payrolls')
            ->leftJoin('users as payer', 'staff_payrolls.paid_by', '=', 'payer.id')
            ->where('staff_payrolls.user_id', $userId);

        if ($hasBank) {
            $query->leftJoin('bank_accounts', 'staff_payrolls.bank_account_id', '=', 'bank_accounts.id')
                ->select(
                    'staff_payrolls.*',
                    'bank_accounts.bank_name',
                    'bank_accounts.account_name',
                    'bank_accounts.account_number',
                    'payer.name as disburser_name'
                );
        } else {
            $query->select(
                'staff_payrolls.*',
                DB::raw("NULL as bank_name"),
                DB::raw("NULL as account_name"),
                DB::raw("NULL as account_number"),
                'payer.name as disburser_name'
            );
        }

        $records = $query->orderByDesc('staff_payrolls.salary_year')
            ->orderByDesc('staff_payrolls.salary_month')
            ->get();

        $totalPaid = (float) $records->where('status', 'paid')->sum('net_salary');
        $totalPending = (float) $records->where('status', 'pending')->sum('net_salary');

        return [
            'staff'         => $staff,
            'history'       => $records,
            'total_paid'    => $totalPaid,
            'total_pending' => $totalPending,
        ];
    }

    /**
     * Get total salary expenses for P&L.
     */
    public static function getTotalSalaryExpense(string|Carbon $startDate, string|Carbon $endDate): float
    {
        if (!Schema::hasTable('staff_payrolls') || !Schema::hasColumn('staff_payrolls', 'status')) {
            return 0.00;
        }

        $start = is_string($startDate) ? Carbon::parse($startDate)->startOfDay() : $startDate->copy()->startOfDay();
        $end = is_string($endDate) ? Carbon::parse($endDate)->endOfDay() : $endDate->copy()->endOfDay();

        $dateCol = Schema::hasColumn('staff_payrolls', 'paid_at') ? 'paid_at' : 'updated_at';

        return (float) DB::table('staff_payrolls')
            ->where('status', 'paid')
            ->whereBetween($dateCol, [$start, $end])
            ->sum('net_salary');
    }
}
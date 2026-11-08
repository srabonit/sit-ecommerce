<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PayrollService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayrollController extends Controller
{
    /**
     * 1. Monthly Payroll Dashboard & Salary Sheet
     */
    public function index(Request $request)
    {
        PayrollService::ensurePayrollTable();

        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);

        $hasBankAccounts = Schema::hasTable('bank_accounts');
        $hasUserSalary   = Schema::hasColumn('users', 'salary');
        $hasUserJoining  = Schema::hasColumn('users', 'joining_date');

        $salarySelectExpr  = $hasUserSalary ? 'users.salary as defined_salary' : DB::raw('0.00 as defined_salary');
        $joiningSelectExpr = $hasUserJoining ? 'users.joining_date' : DB::raw('NULL as joining_date');

        $query = DB::table('staff_payrolls')
            ->join('users', 'staff_payrolls.user_id', '=', 'users.id')
            ->leftJoin('users as payer', 'staff_payrolls.paid_by', '=', 'payer.id')
            ->where('staff_payrolls.salary_year', $year)
            ->where('staff_payrolls.salary_month', $month);

        if ($hasBankAccounts) {
            $query->leftJoin('bank_accounts', 'staff_payrolls.bank_account_id', '=', 'bank_accounts.id')
                ->select(
                    'staff_payrolls.*',
                    'users.name as staff_name',
                    'users.email as staff_email',
                    'users.phone as staff_phone',
                    'users.role as staff_role',
                    $salarySelectExpr,
                    $joiningSelectExpr,
                    'bank_accounts.bank_name',
                    'bank_accounts.account_name',
                    'payer.name as disburser_name'
                );
        } else {
            $query->select(
                'staff_payrolls.*',
                'users.name as staff_name',
                'users.email as staff_email',
                'users.phone as staff_phone',
                'users.role as staff_role',
                $salarySelectExpr,
                $joiningSelectExpr,
                DB::raw("NULL as bank_name"),
                DB::raw("NULL as account_name"),
                'payer.name as disburser_name'
            );
        }

        // Safe Status Filter (?status=pending or ?status=paid)
        if ($request->filled('status') && Schema::hasColumn('staff_payrolls', 'status')) {
            $query->where('staff_payrolls.status', $request->status);
        }

        $payrolls = $query->orderBy('users.name', 'asc')->get();

        // Calculate period statistics
        $totalPayable = (float) $payrolls->sum('net_salary');
        $totalPaid = (float) $payrolls->where('status', 'paid')->sum('net_salary');
        $totalPending = (float) $payrolls->where('status', 'pending')->sum('net_salary');
        $staffCount = $payrolls->count();

        // Active Bank & Cash Accounts
        $bankAccounts = $hasBankAccounts
            ? DB::table('bank_accounts')->where('is_active', true)->orderByDesc('current_balance')->get()
            : collect();

        // Count eligible staff who don't have payroll generated for this month yet
        $existingUserIds = $payrolls->pluck('user_id')->toArray();
        $missingStaffCount = DB::table('users')
            ->where(function ($q) use ($hasUserSalary) {
                $q->whereIn('role', ['staff', 'employee', 'manager', 'admin']);
                if ($hasUserSalary) {
                    $q->orWhere('salary', '>', 0);
                }
            })
            ->whereNull('deleted_at')
            ->whereNotIn('id', $existingUserIds)
            ->count();

        return view('admin.payroll.index', compact(
            'payrolls',
            'year',
            'month',
            'totalPayable',
            'totalPaid',
            'totalPending',
            'staffCount',
            'missingStaffCount',
            'bankAccounts'
        ));
    }

    /**
     * 2. Auto-Generate Monthly Salary Sheet (30th Day Calculation)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'year'  => 'required|integer|min:2020|max:2035',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = (int) $request->year;
        $month = (int) $request->month;

        try {
            $results = PayrollService::generateMonthlyPayroll($year, $month, Auth::id());
            $count = count($results);

            $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} staff salary records processed successfully for {$monthName}.",
                    'data'    => $results,
                ]);
            }

            return redirect()->route('admin.payroll.index', ['year' => $year, 'month' => $month])
                ->with('success', "Salary sheet generated successfully for {$monthName}. Total records: {$count}.");
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error generating payroll: ' . $e->getMessage());
        }
    }

    /**
     * 3. Disburse Salary (Pay from Bank & Auto-record into Expenses)
     */
    public function disburse(Request $request)
    {
        $hasBank = Schema::hasTable('bank_accounts');

        $rules = [
            'payroll_id'     => 'required|integer|exists:staff_payrolls,id',
            'payment_method' => 'required|string',
            'notes'          => 'nullable|string|max:500',
        ];

        if ($hasBank) {
            $rules['bank_account_id'] = 'required|integer|exists:bank_accounts,id';
        } else {
            $rules['bank_account_id'] = 'nullable|integer';
        }

        $request->validate($rules);

        try {
            $result = PayrollService::disburseSalary(
                (int) $request->payroll_id,
                (int) ($request->bank_account_id ?? 0),
                $request->payment_method,
                $request->notes,
                Auth::id()
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Salary disbursed successfully. Voucher: #{$result['voucher_no']}, Amount: ৳" . number_format($result['amount_paid'], 2),
                    'data'    => $result,
                ]);
            }

            return back()->with('success', "Salary of ৳" . number_format($result['amount_paid'], 2) . " disbursed successfully.");
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 4. Adjust Bonus, Deductions or Working Days before payment
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'payroll_id'  => 'required|integer|exists:staff_payrolls,id',
            'worked_days' => 'required|integer|min:1|max:30',
            'bonus'       => 'nullable|numeric|min:0',
            'deduction'   => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string|max:500',
        ]);

        try {
            $payroll = DB::table('staff_payrolls')->where('id', $request->payroll_id)->first();
            if (!$payroll || ($payroll->status ?? '') === 'paid') {
                throw new Exception("Paid salary records cannot be modified.");
            }

            $dailyRate = round((float)$payroll->basic_salary / 30, 2);
            $workedDays = (int) $request->worked_days;
            $baseAmount = round($workedDays * $dailyRate, 2);
            $bonus = (float) ($request->bonus ?? 0);
            $deduction = (float) ($request->deduction ?? 0);

            $netSalary = max(0.00, ($baseAmount + $bonus) - $deduction);

            DB::table('staff_payrolls')->where('id', $payroll->id)->update([
                'worked_days' => $workedDays,
                'bonus'       => $bonus,
                'deduction'   => $deduction,
                'net_salary'  => $netSalary,
                'notes'       => $request->notes ?? $payroll->notes,
                'updated_at'  => now(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'Salary details adjusted successfully.',
                    'net_salary' => $netSalary,
                ]);
            }

            return back()->with('success', 'Salary adjusted successfully.');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 5. Individual Staff Salary Paid History (Admin & Staff Portal)
     */
    public function staffSalaryHistory($userId)
    {
        $data = PayrollService::getStaffSalaryHistory((int) $userId);

        if (!$data['staff']) {
            return redirect()->route('admin.roles.users')->with('error', 'Staff member not found.');
        }

        return view('admin.users.salary_history', [
            'staff'         => $data['staff'],
            'history'       => $data['history'],
            'totalPaid'     => $data['total_paid'],
            'totalPending'  => $data['total_pending'],
        ]);
    }

    /**
     * 6. Print Salary Slip / Payment Voucher
     */
    public function printSlip($id)
    {
        $hasBankAccounts = Schema::hasTable('bank_accounts');
        $hasUserSalary   = Schema::hasColumn('users', 'salary');
        $hasUserJoining  = Schema::hasColumn('users', 'joining_date');

        $salarySelectExpr  = $hasUserSalary ? 'users.salary as defined_salary' : DB::raw('0.00 as defined_salary');
        $joiningSelectExpr = $hasUserJoining ? 'users.joining_date' : DB::raw('NULL as joining_date');

        $query = DB::table('staff_payrolls')
            ->join('users', 'staff_payrolls.user_id', '=', 'users.id')
            ->leftJoin('users as payer', 'staff_payrolls.paid_by', '=', 'payer.id')
            ->where('staff_payrolls.id', $id);

        if ($hasBankAccounts) {
            $query->leftJoin('bank_accounts', 'staff_payrolls.bank_account_id', '=', 'bank_accounts.id')
                ->select(
                    'staff_payrolls.*',
                    'users.name as staff_name',
                    'users.email as staff_email',
                    'users.phone as staff_phone',
                    'users.role as staff_role',
                    $salarySelectExpr,
                    $joiningSelectExpr,
                    'bank_accounts.bank_name',
                    'bank_accounts.account_name',
                    'bank_accounts.account_number',
                    'payer.name as disburser_name'
                );
        } else {
            $query->select(
                'staff_payrolls.*',
                'users.name as staff_name',
                'users.email as staff_email',
                'users.phone as staff_phone',
                'users.role as staff_role',
                $salarySelectExpr,
                $joiningSelectExpr,
                DB::raw("NULL as bank_name"),
                DB::raw("NULL as account_name"),
                DB::raw("NULL as account_number"),
                'payer.name as disburser_name'
            );
        }

        $payroll = $query->first();

        if (!$payroll) {
            return back()->with('error', 'Salary voucher not found.');
        }

        return view('admin.payroll.payslip', compact('payroll'));
    }
}
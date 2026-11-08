<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\StoreSupplierPaymentRequest;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('supplier_payments')
            ->leftJoin('suppliers', 'supplier_payments.supplier_id', '=', 'suppliers.id')
            ->select(
                'supplier_payments.*',
                'suppliers.name as supplier_name',
                'suppliers.company_name as supplier_company'
            );

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'supplier_payments.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('bank_accounts')) {
            $query->leftJoin('bank_accounts', 'supplier_payments.bank_account_id', '=', 'bank_accounts.id')
                  ->addSelect('bank_accounts.account_name as bank_account_name');
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                if (Schema::hasColumn('supplier_payments', 'voucher_number')) {
                    $q->orWhere('supplier_payments.voucher_number', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('supplier_payments', 'payment_number')) {
                    $q->orWhere('supplier_payments.payment_number', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('supplier_payments', 'reference_no')) {
                    $q->orWhere('supplier_payments.reference_no', 'like', "%{$search}%");
                }
                $q->orWhere('suppliers.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_payments.supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('payment_method')) {
            $query->where('supplier_payments.payment_method', $request->get('payment_method'));
        }

        if ($request->filled('payment_type')) {
            $query->where('supplier_payments.payment_type', $request->get('payment_type'));
        }

        if ($request->filled('payment_date')) {
            $query->whereDate('supplier_payments.payment_date', $request->get('payment_date'));
        }

        $supplierPayments = $query->orderBy('supplier_payments.id', 'desc')->paginate(15)->withQueryString();
        $payments = $supplierPayments;

        foreach ($supplierPayments as $p) {
            $vNo = $p->voucher_number ?? ($p->payment_number ?? ('SPV-' . $p->id));
            $p->voucher_number = $vNo;
            $p->payment_number = $vNo;
            $p->supplier = (object)[
                'id'           => $p->supplier_id,
                'name'         => $p->supplier_name,
                'company_name' => $p->supplier_company,
            ];
            $p->bankAccount = (object)[
                'account_name' => $p->bank_account_name ?? 'Cash in Hand',
            ];
        }

        $totalPaidAmount    = DB::table('supplier_payments')->where('status', '!=', 'cancelled')->sum('amount') ?? 0;
        $totalAdvanceAmount = DB::table('supplier_payments')->where('status', '!=', 'cancelled')->where('payment_type', 'advance')->sum('amount') ?? 0;
        $totalVouchersCount = $supplierPayments->total();
        $totalPayablesDue   = Schema::hasColumn('suppliers', 'current_balance') ? (DB::table('suppliers')->where('current_balance', '>', 0)->sum('current_balance') ?? 0) : 0;
        
        $allocAmountCol = Schema::hasColumn('supplier_payment_allocations', 'allocated_amount') ? 'allocated_amount' : 'amount';
        $totalAllocatedAmount = Schema::hasTable('supplier_payment_allocations')
            ? (DB::table('supplier_payment_allocations')->sum($allocAmountCol) ?? 0)
            : 0;

        $suppliers    = DB::table('suppliers')->where('status', 'active')->get();
        $bankAccounts = Schema::hasTable('bank_accounts') ? DB::table('bank_accounts')->get() : collect([]);

        return view('admin.procurement.supplier_payments.index', compact(
            'supplierPayments',
            'payments',
            'totalPaidAmount',
            'totalAdvanceAmount',
            'totalVouchersCount',
            'totalPayablesDue',
            'suppliers',
            'bankAccounts'
        ))->with([
            'totalDisbursedAmount' => $totalPaidAmount,
            'totalAllocatedAmount' => $totalAllocatedAmount,
        ]);
    }

    public function create(Request $request)
    {
        $suppliers    = DB::table('suppliers')->where('status', 'active')->get();
        $bankAccounts = Schema::hasTable('bank_accounts') ? DB::table('bank_accounts')->get() : collect([]);
        $branches     = Schema::hasTable('branches') ? DB::table('branches')->get() : collect([]);

        $selectedSupplier = null;
        $unpaidPurchases  = collect([]);

        if ($request->filled('supplier_id')) {
            $selectedSupplier = DB::table('suppliers')->where('id', $request->supplier_id)->first();
            if ($selectedSupplier) {
                $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchase_no' : 'id');
                $unpaidPurchases = DB::table('purchases')
                    ->where('supplier_id', $selectedSupplier->id)
                    ->where(function ($q) {
                        $q->where('payment_status', '!=', 'paid')
                          ->orWhereNull('payment_status');
                    })
                    ->where('due_amount', '>', 0)
                    ->select(
                        'id',
                        DB::raw("COALESCE({$numberCol}, id) as purchase_number"),
                        'purchase_date',
                        'due_date',
                        'grand_total',
                        'paid_amount',
                        'due_amount'
                    )
                    ->orderBy('id', 'asc')
                    ->get();
            }
        }

        return view('admin.procurement.supplier_payments.create', compact(
            'suppliers',
            'bankAccounts',
            'branches',
            'selectedSupplier',
            'unpaidPurchases'
        ));
    }

    public function store(StoreSupplierPaymentRequest $request)
    {
        $validated = $request->validated();

        $rawType = $validated['payment_type'] ?? $request->input('payment_type', 'invoice_payment');
        $finalPaymentType = in_array($rawType, ['advance', 'opening_balance']) ? $rawType : 'invoice_payment';

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment_file')) {
                $attachmentPath = $request->file('attachment_file')->store('procurement/payments', 'public');
            }

            $amount = (float) $validated['amount'];
            $allocations = $request->input('allocations', []);

            $voucherNumber = $validated['voucher_number'] 
                ?: ($request->input('voucher_number') 
                ?: ($request->input('payment_number') 
                ?: ('SPV-' . date('Ymd') . '-' . strtoupper(Str::random(4)))));

            $insertData = [
                'supplier_id'     => (int) $validated['supplier_id'],
                'payment_date'    => $validated['payment_date'] ?? date('Y-m-d'),
                'payment_method'  => $validated['payment_method'] ?? 'cash',
                'payment_type'    => $finalPaymentType,
                'amount'          => $amount,
                'status'          => 'posted',
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            if (Schema::hasColumn('supplier_payments', 'voucher_number')) {
                $insertData['voucher_number'] = $voucherNumber;
            }
            if (Schema::hasColumn('supplier_payments', 'payment_number')) {
                $insertData['payment_number'] = $voucherNumber;
            }
            if (Schema::hasColumn('supplier_payments', 'branch_id')) {
                $insertData['branch_id'] = !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null;
            }
            if (Schema::hasColumn('supplier_payments', 'bank_account_id')) {
                $insertData['bank_account_id'] = !empty($validated['bank_account_id']) ? (int)$validated['bank_account_id'] : null;
            }
            if (Schema::hasColumn('supplier_payments', 'allocated_amount')) {
                $insertData['allocated_amount'] = 0.00;
            }
            if (Schema::hasColumn('supplier_payments', 'reference_no')) {
                $insertData['reference_no'] = $validated['reference_no'] ?? $request->input('reference_no');
            }
            if (Schema::hasColumn('supplier_payments', 'cheque_no')) {
                $insertData['cheque_no'] = $validated['cheque_no'] ?? $request->input('cheque_no');
            }
            if (Schema::hasColumn('supplier_payments', 'cheque_date')) {
                $insertData['cheque_date'] = $request->input('cheque_date') ?: null;
            }
            if (Schema::hasColumn('supplier_payments', 'attachment_file')) {
                $insertData['attachment_file'] = $attachmentPath;
            }
            if (Schema::hasColumn('supplier_payments', 'notes')) {
                $insertData['notes'] = $validated['notes'] ?? $request->input('notes');
            }
            if (Schema::hasColumn('supplier_payments', 'created_by')) {
                $insertData['created_by'] = Auth::id() ?: 1;
            }

            $voucherId = DB::table('supplier_payments')->insertGetId($insertData);

            $totalAllocated = 0;

            if (!empty($allocations) && is_array($allocations)) {
                foreach ($allocations as $key => $value) {
                    $purchaseId = null;
                    $allocAmount = 0;

                    if (is_array($value)) {
                        $purchaseId = $value['purchase_id'] ?? $key;
                        $allocAmount = (float) ($value['amount'] ?? ($value['allocated_amount'] ?? 0));
                    } else {
                        $purchaseId = $key;
                        $allocAmount = (float) $value;
                    }

                    if (!$purchaseId || $allocAmount <= 0) continue;

                    $purchase = DB::table('purchases')->where('id', $purchaseId)->first();
                    if ($purchase) {
                        $currGrand = (float) ($purchase->grand_total ?? 0);
                        $currPaid  = (float) ($purchase->paid_amount ?? 0);
                        $newPaid   = min($currGrand, $currPaid + $allocAmount);
                        $newDue    = max(0, $currGrand - $newPaid);
                        $newStatus = ($newDue <= 0) ? 'paid' : 'partial';

                        $purchUpdates = [
                            'paid_amount' => $newPaid,
                            'due_amount'  => $newDue,
                            'updated_at'  => now(),
                        ];
                        if (Schema::hasColumn('purchases', 'payment_status')) {
                            $purchUpdates['payment_status'] = $newStatus;
                        }
                        DB::table('purchases')->where('id', $purchaseId)->update($purchUpdates);

                        if (Schema::hasTable('supplier_payment_allocations')) {
                            $allocData = [
                                'supplier_payment_id' => $voucherId,
                                'purchase_id'         => $purchaseId,
                                'created_at'          => now(),
                                'updated_at'          => now(),
                            ];
                            if (Schema::hasColumn('supplier_payment_allocations', 'allocated_amount')) {
                                $allocData['allocated_amount'] = $allocAmount;
                            }
                            if (Schema::hasColumn('supplier_payment_allocations', 'amount')) {
                                $allocData['amount'] = $allocAmount;
                            }
                            DB::table('supplier_payment_allocations')->insert($allocData);
                        }

                        $totalAllocated += $allocAmount;
                    }
                }
            }

            if (Schema::hasColumn('supplier_payments', 'allocated_amount')) {
                DB::table('supplier_payments')->where('id', $voucherId)->update([
                    'allocated_amount' => $totalAllocated,
                    'updated_at'       => now(),
                ]);
            }

            // Deduct Supplier Accounts Payable Liability
            if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'current_balance')) {
                DB::table('suppliers')->where('id', (int)$validated['supplier_id'])->decrement('current_balance', $amount);
            }

            // Deduct Bank Account Balance if table exists
            if (!empty($validated['bank_account_id']) && Schema::hasTable('bank_accounts')) {
                if (Schema::hasColumn('bank_accounts', 'current_balance')) {
                    DB::table('bank_accounts')->where('id', (int)$validated['bank_account_id'])->decrement('current_balance', $amount);
                } elseif (Schema::hasColumn('bank_accounts', 'balance')) {
                    DB::table('bank_accounts')->where('id', (int)$validated['bank_account_id'])->decrement('balance', $amount);
                }
            }

            // General Ledger Postings
            if (Schema::hasTable('account_ledgers')) {
                try {
                    // Dr: Accounts Payable
                    DB::table('account_ledgers')->insert([
                        'chart_of_account_id' => (int) $validated['supplier_id'],
                        'reference_type'      => 'supplier_payment',
                        'reference_id'        => $voucherId,
                        'reference_number'    => $voucherNumber,
                        'date'                => $validated['payment_date'] ?? date('Y-m-d'),
                        'debit'               => $amount,
                        'credit'              => 0,
                        'description'         => 'Payment Disbursement to Supplier #' . $voucherNumber,
                        'branch_id'           => !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);

                    // Cr: Bank / Cash Outflow
                    $disbursingAccountHead = !empty($validated['bank_account_id']) ? (int)$validated['bank_account_id'] : 1;
                    DB::table('account_ledgers')->insert([
                        'chart_of_account_id' => $disbursingAccountHead,
                        'reference_type'      => 'supplier_payment',
                        'reference_id'        => $voucherId,
                        'reference_number'    => $voucherNumber,
                        'date'                => $validated['payment_date'] ?? date('Y-m-d'),
                        'debit'               => 0,
                        'credit'              => $amount,
                        'description'         => 'Bank/Cash Outflow for Payment Voucher #' . $voucherNumber,
                        'branch_id'           => !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                } catch (\Throwable $ledgerEx) {}
            }

            DB::commit();
            return redirect()->route('admin.supplier-payments.show', $voucherId)
                ->with('success', "Payment Voucher #{$voucherNumber} successfully disbursed and recorded.");
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return redirect()->back()->withInput()->with('error', 'Payment recording failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $payment = DB::table('supplier_payments')
            ->leftJoin('suppliers', 'supplier_payments.supplier_id', '=', 'suppliers.id')
            ->where('supplier_payments.id', $id)
            ->select(
                'supplier_payments.*',
                'suppliers.name as supplier_name',
                'suppliers.company_name as supplier_company',
                'suppliers.phone as supplier_phone',
                'suppliers.email as supplier_email',
                'suppliers.address as supplier_address'
            );

        if (Schema::hasTable('branches')) {
            $payment->leftJoin('branches', 'supplier_payments.branch_id', '=', 'branches.id')
                    ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('bank_accounts')) {
            $payment->leftJoin('bank_accounts', 'supplier_payments.bank_account_id', '=', 'bank_accounts.id')
                    ->addSelect('bank_accounts.account_name as bank_account_name');
        }

        $payment = $payment->first();

        if (!$payment) {
            abort(404, 'Payment voucher not found.');
        }

        $vNo = $payment->voucher_number ?? ($payment->payment_number ?? ('SPV-' . $payment->id));
        $payment->voucher_number = $vNo;
        $payment->payment_number = $vNo;

        $allocations = collect([]);
        if (Schema::hasTable('supplier_payment_allocations')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchases.purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchases.purchase_no' : 'purchases.id');
            $allocAmountCol = Schema::hasColumn('supplier_payment_allocations', 'allocated_amount') ? 'supplier_payment_allocations.allocated_amount' : 'supplier_payment_allocations.amount';

            $allocations = DB::table('supplier_payment_allocations')
                ->leftJoin('purchases', 'supplier_payment_allocations.purchase_id', '=', 'purchases.id')
                ->where('supplier_payment_allocations.supplier_payment_id', $id)
                ->select(
                    'supplier_payment_allocations.*',
                    DB::raw("{$allocAmountCol} as allocated_amount"),
                    DB::raw("{$allocAmountCol} as amount"),
                    DB::raw("COALESCE({$numberCol}, 'Bill') as purchase_number"),
                    'purchases.purchase_date',
                    'purchases.grand_total',
                    'purchases.paid_amount',
                    'purchases.due_amount'
                )
                ->get();
        }

        $payment->supplier = (object)[
            'id'           => $payment->supplier_id,
            'name'         => $payment->supplier_name,
            'company_name' => $payment->supplier_company,
            'phone'        => $payment->supplier_phone,
            'email'        => $payment->supplier_email,
            'address'      => $payment->supplier_address,
        ];
        $payment->bankAccount = (object)[
            'account_name'   => $payment->bank_account_name ?? 'Cash in Hand',
            'account_number' => '—',
        ];
        $payment->branch      = (object)['name' => $payment->branch_name ?? 'HQ Branch'];
        $payment->allocations = $allocations;

        return view('admin.procurement.supplier_payments.show', [
            'payment'         => $payment,
            'supplierPayment' => $payment,
            'allocations'     => $allocations,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $payment = DB::table('supplier_payments')->where('id', $id)->first();
        if (!$payment) {
            return redirect()->back()->with('error', 'Payment voucher not found.');
        }

        if ($payment->status === 'cancelled') {
            return redirect()->back()->with('warning', 'This payment voucher has already been cancelled.');
        }

        DB::beginTransaction();
        try {
            $amount = (float) $payment->amount;

            if (Schema::hasTable('supplier_payment_allocations')) {
                $allocations = DB::table('supplier_payment_allocations')->where('supplier_payment_id', $id)->get();
                foreach ($allocations as $alloc) {
                    $allocVal = (float) ($alloc->allocated_amount ?? ($alloc->amount ?? 0));
                    $purchase = DB::table('purchases')->where('id', $alloc->purchase_id)->first();
                    if ($purchase) {
                        $restoredPaid = max(0, (float)($purchase->paid_amount ?? 0) - $allocVal);
                        $restoredDue  = min((float)($purchase->grand_total ?? 0), (float)($purchase->grand_total ?? 0) - $restoredPaid);
                        $restoredStatus = ($restoredPaid <= 0) ? 'unpaid' : 'partial';

                        $pUpdates = [
                            'paid_amount' => $restoredPaid,
                            'due_amount'  => $restoredDue,
                            'updated_at'  => now(),
                        ];
                        if (Schema::hasColumn('purchases', 'payment_status')) {
                            $pUpdates['payment_status'] = $restoredStatus;
                        }
                        DB::table('purchases')->where('id', $alloc->purchase_id)->update($pUpdates);
                    }
                }
            }

            if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'current_balance')) {
                DB::table('suppliers')->where('id', $payment->supplier_id)->increment('current_balance', $amount);
            }

            if (!empty($payment->bank_account_id) && Schema::hasTable('bank_accounts')) {
                if (Schema::hasColumn('bank_accounts', 'current_balance')) {
                    DB::table('bank_accounts')->where('id', $payment->bank_account_id)->increment('current_balance', $amount);
                } elseif (Schema::hasColumn('bank_accounts', 'balance')) {
                    DB::table('bank_accounts')->where('id', $payment->bank_account_id)->increment('balance', $amount);
                }
            }

            if (Schema::hasTable('account_ledgers')) {
                DB::table('account_ledgers')->where([
                    'reference_type' => 'supplier_payment',
                    'reference_id'   => $id,
                ])->delete();
            }

            $vNumber = $payment->voucher_number ?? ($payment->payment_number ?? ('SPV-' . $payment->id));

            DB::table('supplier_payments')->where('id', $id)->update([
                'status'           => 'cancelled',
                'cancelled_reason' => $request->cancel_reason,
                'notes'            => trim(($payment->notes ?? '') . "\n[Void Reason]: " . $request->cancel_reason),
                'updated_at'       => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Payment Voucher #{$vNumber} has been voided successfully.");
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return redirect()->back()->with('error', 'Cancellation failed: ' . $e->getMessage());
        }
    }

    public function getUnpaidPurchases($supplier)
    {
        try {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchase_no' : 'id');

            $bills = DB::table('purchases')
                ->where('supplier_id', $supplier)
                ->where(function ($q) {
                    $q->where('payment_status', '!=', 'paid')
                      ->orWhereNull('payment_status');
                })
                ->where('due_amount', '>', 0)
                ->select(
                    'id',
                    DB::raw("COALESCE({$numberCol}, id) as purchase_number"),
                    'purchase_date',
                    'due_date',
                    'grand_total',
                    'paid_amount',
                    'due_amount',
                    'payment_status'
                )
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $bills,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unpaid bills: ' . $e->getMessage(),
            ], 500);
        }
    }
}
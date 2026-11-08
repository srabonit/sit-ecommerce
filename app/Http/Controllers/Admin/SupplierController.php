<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreSupplierRequest;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers with financial balances.
     */
    public function index(Request $request)
    {
        $query = DB::table('suppliers');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('bin_number', 'like', "%{$search}%")
                  ->orWhere('tax_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('balance_filter')) {
            $filter = $request->get('balance_filter');
            if ($filter === 'due') {
                $query->where('current_balance', '>', 0);
            } elseif ($filter === 'advance') {
                $query->where('current_balance', '<', 0);
            } elseif ($filter === 'zero') {
                $query->where('current_balance', 0);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $suppliers = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Metrics Summary
        $totalPayablesDue    = DB::table('suppliers')->where('current_balance', '>', 0)->sum('current_balance') ?? 0;
        $totalAdvanceDeposits = abs(DB::table('suppliers')->where('current_balance', '<', 0)->sum('current_balance') ?? 0);
        $activeCount         = DB::table('suppliers')->where('status', 'active')->count();

        return view('admin.procurement.suppliers.index', compact(
            'suppliers',
            'totalPayablesDue',
            'totalAdvanceDeposits',
            'activeCount'
        ));
    }

    /**
     * Show the form for onboarding a new supplier.
     */
    public function create()
    {
        $categories = collect([]);

        if (Schema::hasTable('categories')) {
            $catQuery = DB::table('categories');

            if (Schema::hasColumn('categories', 'status')) {
                $catQuery->where('status', 'active');
            } elseif (Schema::hasColumn('categories', 'is_active')) {
                $catQuery->where('is_active', 1);
            }

            if (Schema::hasColumn('categories', 'name')) {
                $catQuery->orderBy('name', 'asc');
            } elseif (Schema::hasColumn('categories', 'title')) {
                $catQuery->orderBy('title', 'asc');
            }

            $categories = $catQuery->get();
        }

        return view('admin.procurement.suppliers.create', compact('categories'));
    }

    /**
     * Store a newly registered supplier and post initial opening balance.
     */
    public function store(StoreSupplierRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $kycPath = null;
            if ($request->hasFile('kyc_document')) {
                $kycPath = $request->file('kyc_document')->store('suppliers/kyc', 'public');
            }

            $openingAmount = (float) ($request->input('opening_balance', 0));
            $balanceType   = $request->input('opening_balance_type', 'payable');
            
            // Payable means we owe supplier (+), Advance means supplier owes us (-)
            $initialBalance = ($balanceType === 'payable') ? $openingAmount : (-$openingAmount);

            $insertData = [
                'name'                 => $validated['name'],
                'phone'                => $validated['phone'],
                'address'              => $validated['address'],
                'created_at'           => now(),
                'updated_at'           => now(),
            ];

            $optionalFields = [
                'company_name'         => $request->input('company_name'),
                'contact_person'       => $request->input('contact_person'),
                'alt_phone'            => $request->input('alt_phone'),
                'email'                => $request->input('email'),
                'status'               => $request->input('status', 'active'),
                'bin_number'           => $request->input('bin_number'),
                'tax_number'           => $request->input('tax_number'),
                'trade_license_no'     => $request->input('trade_license_no'),
                'category_id'          => $request->input('category_id'),
                'payment_terms'        => $request->input('payment_terms', 'net_30'),
                'credit_limit'         => (float) ($request->input('credit_limit', 0)),
                'opening_balance'      => $initialBalance,
                'current_balance'      => $initialBalance,
                'opening_balance_date' => $request->input('opening_balance_date', date('Y-m-d')),
                'bank_name'            => $request->input('bank_name'),
                'bank_branch'          => $request->input('bank_branch'),
                'bank_account_name'    => $request->input('bank_account_name'),
                'bank_account_no'      => $request->input('bank_account_no'),
                'bank_routing_no'      => $request->input('bank_routing_no'),
                'swift_code'           => $request->input('swift_code'),
                'mfs_number'           => $request->input('mfs_number'),
                'currency'             => $request->input('currency', 'BDT'),
                'city'                 => $validated['city'] ?? 'Dhaka',
                'postal_code'          => $request->input('postal_code'),
                'country'              => $validated['country'] ?? 'Bangladesh',
                'kyc_document'         => $kycPath,
                'notes'                => $request->input('notes'),
            ];

            foreach ($optionalFields as $col => $val) {
                if (Schema::hasColumn('suppliers', $col)) {
                    $insertData[$col] = $val;
                }
            }

            $supplierId = DB::table('suppliers')->insertGetId($insertData);

            // Opening Balance Ledger Entry
            if (Schema::hasTable('account_ledgers') && $openingAmount > 0) {
                DB::table('account_ledgers')->insert([
                    'chart_of_account_id' => $supplierId,
                    'reference_type'      => 'supplier_opening_balance',
                    'reference_id'        => $supplierId,
                    'reference_number'    => 'OPB-' . str_pad($supplierId, 4, '0', STR_PAD_LEFT),
                    'date'                => $request->input('opening_balance_date', date('Y-m-d')),
                    'debit'               => ($balanceType === 'advance') ? $openingAmount : 0,
                    'credit'              => ($balanceType === 'payable') ? $openingAmount : 0,
                    'description'         => 'Initial Opening Balance Migration',
                    'branch_id'           => null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.suppliers.ledger', $supplierId)
                ->with('success', 'Supplier onboarded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to onboard supplier: ' . $e->getMessage());
        }
    }

    /**
     * Show supplier details (Redirects to Ledger).
     */
    public function show($id)
    {
        return redirect()->route('admin.suppliers.ledger', $id);
    }

    /**
     * Show edit form for supplier profile.
     */
    public function edit($id)
    {
        $supplier = DB::table('suppliers')->where('id', $id)->first();
        if (!$supplier) {
            abort(404, 'Supplier not found.');
        }

        $categories = collect([]);
        if (Schema::hasTable('categories')) {
            $catQuery = DB::table('categories');

            if (Schema::hasColumn('categories', 'status')) {
                $catQuery->where('status', 'active');
            } elseif (Schema::hasColumn('categories', 'is_active')) {
                $catQuery->where('is_active', 1);
            }

            if (Schema::hasColumn('categories', 'name')) {
                $catQuery->orderBy('name', 'asc');
            } elseif (Schema::hasColumn('categories', 'title')) {
                $catQuery->orderBy('title', 'asc');
            }

            $categories = $catQuery->get();
        }

        return view('admin.procurement.suppliers.edit', compact('supplier', 'categories'));
    }

    /**
     * Update supplier profile (Keeps opening balance locked for ledger integrity).
     */
    public function update(Request $request, $id)
    {
        $supplier = DB::table('suppliers')->where('id', $id)->first();
        if (!$supplier) {
            abort(404);
        }

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:50',
            'address'          => 'required|string|max:500',
            'city'             => 'required|string|max:100',
            'country'          => 'required|string|max:100',
            'status'           => 'required|in:active,inactive',
            'payment_terms'    => 'required|string',
            'credit_limit'     => 'nullable|numeric|min:0',
            'currency'         => 'required|string|max:10',
            'kyc_document'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $updateFields = [
            'name'              => $validated['name'],
            'company_name'      => $request->input('company_name'),
            'contact_person'    => $request->input('contact_person'),
            'phone'             => $validated['phone'],
            'alt_phone'         => $request->input('alt_phone'),
            'email'             => $request->input('email'),
            'status'            => $validated['status'],
            'bin_number'        => $request->input('bin_number'),
            'tax_number'        => $request->input('tax_number'),
            'trade_license_no'  => $request->input('trade_license_no'),
            'category_id'       => $request->input('category_id'),
            'payment_terms'     => $validated['payment_terms'],
            'credit_limit'      => (float) ($request->input('credit_limit', 0)),
            'bank_name'         => $request->input('bank_name'),
            'bank_branch'       => $request->input('bank_branch'),
            'bank_account_name' => $request->input('bank_account_name'),
            'bank_account_no'   => $request->input('bank_account_no'),
            'bank_routing_no'   => $request->input('bank_routing_no'),
            'swift_code'        => $request->input('swift_code'),
            'mfs_number'        => $request->input('mfs_number'),
            'currency'          => $validated['currency'],
            'address'           => $validated['address'],
            'city'              => $validated['city'],
            'postal_code'       => $request->input('postal_code'),
            'country'           => $validated['country'],
            'notes'             => $request->input('notes'),
            'updated_at'        => now(),
        ];

        $updateData = [];
        foreach ($updateFields as $col => $val) {
            if (Schema::hasColumn('suppliers', $col)) {
                $updateData[$col] = $val;
            }
        }

        if ($request->hasFile('kyc_document')) {
            if (!empty($supplier->kyc_document)) {
                Storage::disk('public')->delete($supplier->kyc_document);
            }
            if (Schema::hasColumn('suppliers', 'kyc_document')) {
                $updateData['kyc_document'] = $request->file('kyc_document')->store('suppliers/kyc', 'public');
            }
        }

        DB::table('suppliers')->where('id', $id)->update($updateData);

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier profile updated successfully.');
    }

    /**
     * Remove / deactivate supplier.
     */
    public function destroy($id)
    {
        $hasTransactions = DB::table('purchases')->where('supplier_id', $id)->exists()
            || DB::table('supplier_payments')->where('supplier_id', $id)->exists();

        if ($hasTransactions) {
            DB::table('suppliers')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);
            return redirect()->back()->with('warning', 'Supplier has transaction history. Status updated to inactive instead of deletion.');
        }

        DB::table('suppliers')->where('id', $id)->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier removed successfully.');
    }

    /**
     * Generate Comprehensive Running Ledger Statement.
     */
    public function ledger(Request $request, $id)
    {
        $supplier = DB::table('suppliers')->where('id', $id)->first();
        if (!$supplier) {
            abort(404, 'Supplier not found.');
        }

        $fromDate = $request->input('from_date', date('Y-m-01'));
        $toDate   = $request->input('to_date', date('Y-m-d'));
        $type     = $request->input('type');

        // Calculate opening balance prior to $fromDate
        $priorPurchases = DB::table('purchases')
            ->where('supplier_id', $id)
            ->whereDate('purchase_date', '<', $fromDate)
            ->sum('grand_total') ?? 0;

        $priorPayments = DB::table('supplier_payments')
            ->where('supplier_id', $id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('payment_date', '<', $fromDate)
            ->sum('amount') ?? 0;

        $priorReturns = DB::table('purchase_returns')
            ->where('supplier_id', $id)
            ->where('status', 'approved')
            ->whereDate('return_date', '<', $fromDate)
            ->sum('grand_total') ?? 0;

        $priorDebitNotes = DB::table('supplier_debit_credit_notes')
            ->where('supplier_id', $id)
            ->where('status', 'posted')
            ->where('note_type', 'debit_note')
            ->whereDate('note_date', '<', $fromDate)
            ->sum('total_amount') ?? 0;

        $priorCreditNotes = DB::table('supplier_debit_credit_notes')
            ->where('supplier_id', $id)
            ->where('status', 'posted')
            ->where('note_type', 'credit_note')
            ->whereDate('note_date', '<', $fromDate)
            ->sum('total_amount') ?? 0;

        $initialMigration = (float) ($supplier->opening_balance ?? 0);
        $openingBalance = $initialMigration + ($priorPurchases + $priorCreditNotes) - ($priorPayments + $priorReturns + $priorDebitNotes);

        // Fetch periodic transactions
        $transactions = collect([]);

        // Purchases (Credit)
        if (!$type || $type === 'purchase') {
            $bills = DB::table('purchases')
                ->where('supplier_id', $id)
                ->whereBetween('purchase_date', [$fromDate, $toDate])
                ->get()
                ->map(fn($row) => (object)[
                    'date'             => $row->purchase_date,
                    'reference_number' => $row->purchase_number,
                    'secondary_ref'    => $row->supplier_invoice_no,
                    'type'             => 'purchase',
                    'description'      => 'Commercial Purchase Bill',
                    'debit'            => 0,
                    'credit'           => (float) $row->grand_total,
                    'payment_method'   => $row->payment_method,
                    'route'            => route('admin.purchases.show', $row->id),
                    'created_at'       => $row->created_at,
                ]);
            $transactions = $transactions->merge($bills);
        }

        // Payments (Debit)
        if (!$type || $type === 'payment') {
            $pmts = DB::table('supplier_payments')
                ->where('supplier_id', $id)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->get()
                ->map(fn($row) => (object)[
                    'date'             => $row->payment_date,
                    'reference_number' => $row->voucher_number,
                    'secondary_ref'    => $row->reference_no ?? $row->cheque_no,
                    'type'             => 'payment',
                    'description'      => 'Disbursement Settlement (' . ucfirst(str_replace('_', ' ', $row->payment_type)) . ')',
                    'debit'            => (float) $row->amount,
                    'credit'           => 0,
                    'payment_method'   => $row->payment_method,
                    'route'            => route('admin.supplier-payments.show', $row->id),
                    'created_at'       => $row->created_at,
                ]);
            $transactions = $transactions->merge($pmts);
        }

        // Returns (Debit)
        if (!$type || $type === 'return') {
            $rtvs = DB::table('purchase_returns')
                ->where('supplier_id', $id)
                ->where('status', 'approved')
                ->whereBetween('return_date', [$fromDate, $toDate])
                ->get()
                ->map(fn($row) => (object)[
                    'date'             => $row->return_date,
                    'reference_number' => $row->return_number,
                    'secondary_ref'    => $row->reference_no,
                    'type'             => 'return',
                    'description'      => 'Purchase Return / RTV Allowance',
                    'debit'            => (float) $row->grand_total,
                    'credit'           => 0,
                    'payment_method'   => $row->settlement_type,
                    'route'            => route('admin.purchase-returns.show', $row->id),
                    'created_at'       => $row->created_at,
                ]);
            $transactions = $transactions->merge($rtvs);
        }

        // Debit / Credit Notes
        if (!$type || in_array($type, ['debit_note', 'credit_note'])) {
            $notesQuery = DB::table('supplier_debit_credit_notes')
                ->where('supplier_id', $id)
                ->where('status', 'posted')
                ->whereBetween('note_date', [$fromDate, $toDate]);

            if ($type) {
                $notesQuery->where('note_type', $type);
            }

            $notes = $notesQuery->get()
                ->map(fn($row) => (object)[
                    'date'             => $row->note_date,
                    'reference_number' => $row->note_number,
                    'secondary_ref'    => $row->reference_no,
                    'type'             => $row->note_type,
                    'description'      => 'Adjustment: ' . $row->reason,
                    'debit'            => ($row->note_type === 'debit_note') ? (float) $row->total_amount : 0,
                    'credit'           => ($row->note_type === 'credit_note') ? (float) $row->total_amount : 0,
                    'payment_method'   => null,
                    'route'            => route('admin.supplier-debit-credit-notes.show', $row->id),
                    'created_at'       => $row->created_at,
                ]);
            $transactions = $transactions->merge($notes);
        }

        // Exchanges (Differential Delta)
        if (!$type || $type === 'exchange') {
            $exchanges = DB::table('supplier_exchanges')
                ->where('supplier_id', $id)
                ->where('status', 'approved')
                ->whereBetween('exchange_date', [$fromDate, $toDate])
                ->get()
                ->map(function ($row) {
                    $delta = (float) $row->differential_amount;
                    return (object)[
                        'date'             => $row->exchange_date,
                        'reference_number' => $row->exchange_number,
                        'secondary_ref'    => $row->reference_no,
                        'type'             => 'exchange',
                        'description'      => 'Product Swap Valuation Difference',
                        'debit'            => ($delta < 0) ? abs($delta) : 0,
                        'credit'           => ($delta > 0) ? $delta : 0,
                        'payment_method'   => $row->settlement_type,
                        'route'            => route('admin.supplier-exchanges.show', $row->id),
                        'created_at'       => $row->created_at,
                    ];
                });
            $transactions = $transactions->merge($exchanges);
        }

        $ledgerEntries = $transactions->sortBy('date')->values();

        $totalDebit  = $ledgerEntries->sum('debit');
        $totalCredit = $ledgerEntries->sum('credit');
        $closingBalance = $openingBalance + $totalCredit - $totalDebit;

        return view('admin.procurement.suppliers.ledger', compact(
            'supplier',
            'ledgerEntries',
            'openingBalance',
            'totalDebit',
            'totalCredit',
            'closingBalance'
        ));
    }
}
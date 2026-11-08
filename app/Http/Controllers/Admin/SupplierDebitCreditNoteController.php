<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Http\Requests\StoreSupplierDebitCreditNoteRequest;

class SupplierDebitCreditNoteController extends Controller
{
    /**
     * Display a listing of Supplier Debit / Credit Notes.
     */
    public function index(Request $request)
    {
        $query = DB::table('supplier_debit_credit_notes');

        // Defensive Joins
        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'supplier_debit_credit_notes.supplier_id', '=', 'suppliers.id')
                  ->addSelect('suppliers.name as supplier_name', 'suppliers.company_name as supplier_company');
        }

        if (Schema::hasTable('branches') && Schema::hasColumn('supplier_debit_credit_notes', 'branch_id')) {
            $query->leftJoin('branches', 'supplier_debit_credit_notes.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchases.purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchases.purchase_no' : 'purchases.id');
            $query->leftJoin('purchases', 'supplier_debit_credit_notes.purchase_id', '=', 'purchases.id')
                  ->addSelect(DB::raw("COALESCE({$numberCol}, supplier_debit_credit_notes.purchase_id) as purchase_number"));
        }

        $query->addSelect('supplier_debit_credit_notes.*');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('supplier_debit_credit_notes.note_number', 'like', "%{$search}%");
                if (Schema::hasColumn('supplier_debit_credit_notes', 'reference_no')) {
                    $q->orWhere('supplier_debit_credit_notes.reference_no', 'like', "%{$search}%");
                }
                if (Schema::hasTable('suppliers')) {
                    $q->orWhere('suppliers.name', 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_debit_credit_notes.supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('note_type')) {
            $query->where('supplier_debit_credit_notes.note_type', $request->get('note_type'));
        }

        if ($request->filled('status')) {
            $query->where('supplier_debit_credit_notes.status', $request->get('status'));
        }

        if ($request->filled('note_date')) {
            $query->whereDate('supplier_debit_credit_notes.note_date', $request->get('note_date'));
        }

        $notes = $query->orderBy('supplier_debit_credit_notes.id', 'desc')->paginate(15)->withQueryString();

        foreach ($notes as $n) {
            $n->supplier_name = $n->supplier_name ?? '—';
            $n->branch_name   = $n->branch_name ?? 'HQ Branch';
            $n->purchase_number = $n->purchase_number ?? '—';
        }

        // Metrics Summary
        $totalDebitAmount  = DB::table('supplier_debit_credit_notes')->where('note_type', 'debit_note')->where('status', '!=', 'cancelled')->sum('total_amount') ?? 0;
        $totalCreditAmount = DB::table('supplier_debit_credit_notes')->where('note_type', 'credit_note')->where('status', '!=', 'cancelled')->sum('total_amount') ?? 0;
        $draftCount        = DB::table('supplier_debit_credit_notes')->where('status', 'draft')->count();
        $postedCount       = DB::table('supplier_debit_credit_notes')->whereIn('status', ['posted', 'approved'])->count();

        $suppliers = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);

        return view('admin.procurement.supplier_debit_credit_notes.index', compact(
            'notes',
            'totalDebitAmount',
            'totalCreditAmount',
            'draftCount',
            'postedCount',
            'suppliers'
        ));
    }

    /**
     * Show the form for creating a new Debit/Credit Note.
     */
    public function create(Request $request)
    {
        $suppliers = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);
        $branches  = Schema::hasTable('branches') ? DB::table('branches')->get() : collect([]);

        $purchases = collect([]);
        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchase_no' : 'id');
            $purchases = DB::table('purchases')
                ->select('id', DB::raw("COALESCE({$numberCol}, id) as purchase_number"), 'supplier_id', 'grand_total', 'paid_amount', 'purchase_date')
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get();
        }

        $accounts = collect([]);
        if (Schema::hasTable('chart_of_accounts')) {
            $accounts = DB::table('chart_of_accounts')->select('id', 'name', 'code', 'type')->get();
        }

        return view('admin.procurement.supplier_debit_credit_notes.create', compact(
            'suppliers',
            'branches',
            'purchases',
            'accounts'
        ));
    }

    /**
     * Store a newly created Debit/Credit Note and post ledger entries.
     */
    public function store(StoreSupplierDebitCreditNoteRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment_file')) {
                $attachmentPath = $request->file('attachment_file')->store('procurement/notes', 'public');
            }

            $amount      = (float) ($validated['amount'] ?? 0);
            $taxAmount   = (float) ($validated['tax_amount'] ?? 0);
            $totalAmount = max(0.01, $amount + $taxAmount);

            $notePrefix = (($validated['note_type'] ?? 'debit_note') === 'credit_note') ? 'SCN-' : 'SDN-';
            $defaultNumber = $notePrefix . date('Ymd') . '-' . rand(1000, 9999);

            $insertData = [
                'note_type'           => $validated['note_type'] ?? 'debit_note',
                'note_number'         => $validated['note_number'] ?? $defaultNumber,
                'supplier_id'         => (int) $validated['supplier_id'],
                'purchase_id'         => !empty($validated['purchase_id']) ? (int)$validated['purchase_id'] : null,
                'branch_id'           => !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null,
                'note_date'           => $validated['note_date'] ?? date('Y-m-d'),
                'reason_category'     => $validated['reason_category'] ?? ($validated['reason_type'] ?? 'price_adjustment'),
                'reason'              => $validated['reason'] ?? ($validated['description'] ?? 'Adjustment Note'),
                'description'         => $validated['reason'] ?? ($validated['description'] ?? 'Adjustment Note'),
                'reference_no'        => $validated['reference_no'] ?? null,
                'amount'              => $amount,
                'tax_amount'          => $taxAmount,
                'total_amount'        => $totalAmount,
                'chart_of_account_id' => !empty($validated['chart_of_account_id']) ? (int)$validated['chart_of_account_id'] : null,
                'status'              => $validated['status'] ?? 'posted',
                'attachment_file'     => $attachmentPath,
                'notes'               => $validated['notes'] ?? null,
                'created_by'          => auth()->id() ?? 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            // Database column safe filtering
            $existingColumns = Schema::getColumnListing('supplier_debit_credit_notes');
            $safeData = array_intersect_key($insertData, array_flip($existingColumns));

            $noteId = DB::table('supplier_debit_credit_notes')->insertGetId($safeData);

            if (($insertData['status']) === 'posted' || ($insertData['status']) === 'approved') {
                $this->processPostedNote($noteId);
            }

            DB::commit();
            return redirect()->route('admin.supplier-debit-credit-notes.show', $noteId)
                ->with('success', 'Adjustment note created and recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to store adjustment note: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Adjustment Note details.
     */
    public function show($id)
    {
        $query = DB::table('supplier_debit_credit_notes')->where('supplier_debit_credit_notes.id', $id);

        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'supplier_debit_credit_notes.supplier_id', '=', 'suppliers.id')
                  ->addSelect(
                      'suppliers.name as supplier_name',
                      'suppliers.company_name as supplier_company',
                      'suppliers.phone as supplier_phone',
                      'suppliers.email as supplier_email',
                      'suppliers.address as supplier_address'
                  );
        }

        if (Schema::hasTable('branches') && Schema::hasColumn('supplier_debit_credit_notes', 'branch_id')) {
            $query->leftJoin('branches', 'supplier_debit_credit_notes.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchases.purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchases.purchase_no' : 'purchases.id');
            $query->leftJoin('purchases', 'supplier_debit_credit_notes.purchase_id', '=', 'purchases.id')
                  ->addSelect(DB::raw("COALESCE({$numberCol}, supplier_debit_credit_notes.purchase_id) as purchase_number"));
        }

        if (Schema::hasTable('chart_of_accounts') && Schema::hasColumn('supplier_debit_credit_notes', 'chart_of_account_id')) {
            $query->leftJoin('chart_of_accounts', 'supplier_debit_credit_notes.chart_of_account_id', '=', 'chart_of_accounts.id')
                  ->addSelect('chart_of_accounts.name as account_head_name');
        }

        $query->addSelect('supplier_debit_credit_notes.*');
        $note = $query->first();

        if (!$note) {
            abort(404, 'Adjustment note not found.');
        }

        // Blade syntax compatibility wrappers
        $note->supplier = (object)[
            'id'           => $note->supplier_id,
            'name'         => $note->supplier_name ?? '—',
            'company_name' => $note->supplier_company ?? '',
            'phone'        => $note->supplier_phone ?? '—',
            'email'        => $note->supplier_email ?? '—',
            'address'      => $note->supplier_address ?? '—',
        ];
        $note->branch         = (object)['name' => $note->branch_name ?? 'HQ Branch'];
        $note->purchase       = (object)['purchase_number' => $note->purchase_number ?? '—'];
        $note->chartOfAccount = (object)['name' => $note->account_head_name ?? 'Purchase Adjustments'];

        return view('admin.procurement.supplier_debit_credit_notes.show', compact('note'));
    }

    /**
     * Approve and post draft adjustment note to supplier ledger.
     */
    public function approve(Request $request, $id)
    {
        $note = DB::table('supplier_debit_credit_notes')->where('id', $id)->first();
        if (!$note) {
            return redirect()->back()->with('error', 'Adjustment note not found.');
        }

        if (in_array($note->status, ['posted', 'approved'])) {
            return redirect()->back()->with('warning', 'This note has already been posted to the ledger.');
        }

        if ($note->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cancelled notes cannot be approved.');
        }

        DB::beginTransaction();
        try {
            DB::table('supplier_debit_credit_notes')->where('id', $id)->update([
                'status'     => 'posted',
                'updated_at' => now(),
            ]);

            $this->processPostedNote($id);

            DB::commit();
            return redirect()->back()->with('success', "Note #{$note->note_number} has been posted to supplier ledger.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel / Void adjustment note and reverse ledger balance.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $note = DB::table('supplier_debit_credit_notes')->where('id', $id)->first();
        if (!$note) {
            return redirect()->back()->with('error', 'Adjustment note not found.');
        }

        if ($note->status === 'cancelled') {
            return redirect()->back()->with('warning', 'This note is already voided.');
        }

        DB::beginTransaction();
        try {
            if (in_array($note->status, ['posted', 'approved'])) {
                $totalAmount = (float) $note->total_amount;

                // Reverse Supplier current_balance
                if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'current_balance')) {
                    if ($note->note_type === 'debit_note') {
                        DB::table('suppliers')->where('id', $note->supplier_id)->increment('current_balance', $totalAmount);
                    } else {
                        DB::table('suppliers')->where('id', $note->supplier_id)->decrement('current_balance', $totalAmount);
                    }
                }

                // Delete posted ledger entries
                if (Schema::hasTable('account_ledgers')) {
                    DB::table('account_ledgers')->where([
                        'reference_type' => 'supplier_adjustment_note',
                        'reference_id'   => $id,
                    ])->delete();
                }
            }

            $updateData = [
                'status'     => 'cancelled',
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('supplier_debit_credit_notes', 'cancelled_reason')) {
                $updateData['cancelled_reason'] = $request->cancel_reason;
            }
            if (Schema::hasColumn('supplier_debit_credit_notes', 'notes')) {
                $updateData['notes'] = trim(($note->notes ?? '') . "\n[Void Reason]: " . $request->cancel_reason);
            }

            DB::table('supplier_debit_credit_notes')->where('id', $id)->update($updateData);

            DB::commit();
            return redirect()->back()->with('success', "Note #{$note->note_number} has been voided successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Cancellation failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Process ledger and balance adjustments for posted note.
     */
    protected function processPostedNote($noteId)
    {
        $note = DB::table('supplier_debit_credit_notes')->where('id', $noteId)->first();
        if (!$note) return;

        $totalAmount = (float) $note->total_amount;
        $isDebit = ($note->note_type === 'debit_note');

        // Adjust supplier running balance
        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'current_balance')) {
            if ($isDebit) {
                DB::table('suppliers')->where('id', $note->supplier_id)->decrement('current_balance', $totalAmount);
            } else {
                DB::table('suppliers')->where('id', $note->supplier_id)->increment('current_balance', $totalAmount);
            }
        }

        // Post to General Ledger
        if (Schema::hasTable('account_ledgers')) {
            $balancingHead = $note->chart_of_account_id ?? 1;
            $noteReason = $note->reason ?? ($note->description ?? 'Adjustment Note');
            $branchId = $note->branch_id ?? null;

            if ($isDebit) {
                // Dr: Accounts Payable (Reduces Liability)
                DB::table('account_ledgers')->insert([
                    'chart_of_account_id' => $note->supplier_id,
                    'reference_type'      => 'supplier_adjustment_note',
                    'reference_id'        => $noteId,
                    'reference_number'    => $note->note_number,
                    'date'                => $note->note_date,
                    'debit'               => $totalAmount,
                    'credit'              => 0,
                    'description'         => 'Debit Note Allowance: ' . $noteReason,
                    'branch_id'           => $branchId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Cr: Purchase Allowances / Expense Offset
                DB::table('account_ledgers')->insert([
                    'chart_of_account_id' => $balancingHead,
                    'reference_type'      => 'supplier_adjustment_note',
                    'reference_id'        => $noteId,
                    'reference_number'    => $note->note_number,
                    'date'                => $note->note_date,
                    'debit'               => 0,
                    'credit'              => $totalAmount,
                    'description'         => 'Purchase Allowance Offset for Debit Note #' . $note->note_number,
                    'branch_id'           => $branchId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            } else {
                // Dr: Additional Procurement Cost / Expense
                DB::table('account_ledgers')->insert([
                    'chart_of_account_id' => $balancingHead,
                    'reference_type'      => 'supplier_adjustment_note',
                    'reference_id'        => $noteId,
                    'reference_number'    => $note->note_number,
                    'date'                => $note->note_date,
                    'debit'               => $totalAmount,
                    'credit'              => 0,
                    'description'         => 'Expense Addition on Credit Note #' . $note->note_number,
                    'branch_id'           => $branchId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Cr: Accounts Payable (Increases Liability)
                DB::table('account_ledgers')->insert([
                    'chart_of_account_id' => $note->supplier_id,
                    'reference_type'      => 'supplier_adjustment_note',
                    'reference_id'        => $noteId,
                    'reference_number'    => $note->note_number,
                    'date'                => $note->note_date,
                    'debit'               => 0,
                    'credit'              => $totalAmount,
                    'description'         => 'Credit Note Liability Addition: ' . $noteReason,
                    'branch_id'           => $branchId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    }
}
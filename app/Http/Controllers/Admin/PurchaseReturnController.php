<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of Purchase Returns (RTV).
     */
    public function index(Request $request)
    {
        if (!Schema::hasTable('purchase_returns')) {
            $purchaseReturns = new LengthAwarePaginator([], 0, 15);
            $totalReturnValue = 0;
            $pendingCount = 0;
            $completedCount = 0;
            $suppliers = collect([]);

            return view('admin.procurement.purchase_returns.index', compact(
                'purchaseReturns',
                'totalReturnValue',
                'pendingCount',
                'completedCount',
                'suppliers'
            ))->with(['returns' => $purchaseReturns]);
        }

        $query = DB::table('purchase_returns');

        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'purchase_returns.supplier_id', '=', 'suppliers.id')
                  ->addSelect('suppliers.name as supplier_name', 'suppliers.company_name as supplier_company');
        }

        if (Schema::hasTable('warehouses')) {
            $query->leftJoin('warehouses', 'purchase_returns.warehouse_id', '=', 'warehouses.id')
                  ->addSelect('warehouses.name as warehouse_name');
        }

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'purchase_returns.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchases.purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchases.purchase_no' : 'purchases.id');
            $query->leftJoin('purchases', 'purchase_returns.purchase_id', '=', 'purchases.id')
                  ->addSelect(DB::raw("COALESCE({$numberCol}, purchase_returns.purchase_id) as purchase_number"));
        }

        if (Schema::hasTable('purchase_receipts')) {
            $query->leftJoin('purchase_receipts', 'purchase_returns.purchase_receipt_id', '=', 'purchase_receipts.id')
                  ->addSelect('purchase_receipts.receipt_number as grn_number');
        }

        $query->addSelect('purchase_returns.*');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('purchase_returns.return_number', 'like', "%{$search}%");
                if (Schema::hasColumn('purchase_returns', 'reference_no')) {
                    $q->orWhere('purchase_returns.reference_no', 'like', "%{$search}%");
                }
                if (Schema::hasTable('suppliers')) {
                    $q->orWhere('suppliers.name', 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('purchase_returns.supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('settlement_type')) {
            $query->where('purchase_returns.settlement_type', $request->get('settlement_type'));
        }

        if ($request->filled('status')) {
            $query->where('purchase_returns.status', $request->get('status'));
        }

        if ($request->filled('return_date')) {
            $query->whereDate('purchase_returns.return_date', $request->get('return_date'));
        }

        $purchaseReturns = $query->orderBy('purchase_returns.id', 'desc')->paginate(15)->withQueryString();

        foreach ($purchaseReturns as $r) {
            $r->supplier = (object)[
                'id'           => $r->supplier_id,
                'name'         => $r->supplier_name ?? '—',
                'company_name' => $r->supplier_company ?? '',
            ];
            $r->warehouse = (object)['name' => $r->warehouse_name ?? 'Central Warehouse'];
            $r->branch    = (object)['name' => $r->branch_name ?? 'HQ Branch'];
            $r->purchase  = (object)['purchase_number' => $r->purchase_number ?? '—'];
            $r->purchaseReceipt = (object)['receipt_number' => $r->grn_number ?? '—'];
        }

        // Metrics Summary
        $totalReturnValue = DB::table('purchase_returns')->where('status', '!=', 'cancelled')->sum('grand_total') ?? 0;
        $pendingCount     = DB::table('purchase_returns')->where('status', 'pending')->count();
        $completedCount   = DB::table('purchase_returns')->whereIn('status', ['approved', 'completed'])->count();
        $suppliers        = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);

        // Pass both $purchaseReturns and $returns to satisfy any view syntax
        return view('admin.procurement.purchase_returns.index', compact(
            'purchaseReturns',
            'totalReturnValue',
            'pendingCount',
            'completedCount',
            'suppliers'
        ))->with([
            'returns' => $purchaseReturns
        ]);
    }

    /**
     * Show the form for creating a new purchase return note.
     */
    public function create(Request $request)
    {
        $suppliers    = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);
        $warehouses   = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);
        $branches     = Schema::hasTable('branches') ? DB::table('branches')->get() : collect([]);
        $bankAccounts = Schema::hasTable('bank_accounts') ? DB::table('bank_accounts')->get() : collect([]);

        $purchases = collect([]);
        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchase_no' : 'id');
            $purchases = DB::table('purchases')
                ->select('id', DB::raw("COALESCE({$numberCol}, id) as purchase_number"), 'supplier_id', 'warehouse_id', 'grand_total', 'purchase_date')
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get();
        }

        $purchaseReceipts = collect([]);
        if (Schema::hasTable('purchase_receipts')) {
            $purchaseReceipts = DB::table('purchase_receipts')
                ->select('id', 'receipt_number', 'supplier_id', 'warehouse_id', 'receiving_date')
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get();
        }

        return view('admin.procurement.purchase_returns.create', compact(
            'suppliers',
            'warehouses',
            'branches',
            'bankAccounts',
            'purchases',
            'purchaseReceipts'
        ));
    }

    /**
     * Store a newly created Purchase Return in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'     => 'required|integer',
            'warehouse_id'    => 'required|integer',
            'branch_id'       => 'nullable|integer',
            'return_date'     => 'required|date',
            'return_number'   => 'nullable|string|max:100',
            'settlement_type' => 'required|string',
            'bank_account_id' => 'nullable|integer',
            'reference_no'    => 'nullable|string|max:100',
            'status'          => 'required|string|in:approved,pending',
            'notes'           => 'nullable|string',
            'items'           => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity'   => 'required|numeric|gt:0',
            'items.*.unit_cost'  => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment_file')) {
                $attachmentPath = $request->file('attachment_file')->store('procurement/returns', 'public');
            }

            $items = $request->input('items', []);
            $subtotal = 0;
            foreach ($items as $item) {
                $qty  = (float) ($item['quantity'] ?? 1);
                $cost = (float) ($item['unit_cost'] ?? 0);
                $tax  = (float) ($item['tax'] ?? 0);
                $subtotal += ($qty * $cost) + $tax;
            }

            $taxAmount    = (float) $request->input('tax_amount', 0);
            $shippingCost = (float) $request->input('shipping_cost', 0);
            $grandTotal   = max(0.01, $subtotal + $taxAmount - $shippingCost);

            $returnNumber = $validated['return_number'] ?? ('RTV-' . date('Ymd') . '-' . strtoupper(Str::random(4)));

            $insertData = [
                'return_number'       => $returnNumber,
                'purchase_id'         => !empty($request->purchase_id) ? (int)$request->purchase_id : null,
                'purchase_receipt_id' => !empty($request->purchase_receipt_id) ? (int)$request->purchase_receipt_id : null,
                'supplier_id'         => (int) $validated['supplier_id'],
                'warehouse_id'        => (int) $validated['warehouse_id'],
                'branch_id'           => !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null,
                'return_date'         => $validated['return_date'],
                'settlement_type'     => $validated['settlement_type'],
                'bank_account_id'     => !empty($validated['bank_account_id']) ? (int)$validated['bank_account_id'] : null,
                'reference_no'        => $validated['reference_no'] ?? null,
                'subtotal'            => $subtotal,
                'tax_amount'          => $taxAmount,
                'shipping_cost'       => $shippingCost,
                'grand_total'         => $grandTotal,
                'status'              => $validated['status'] ?? 'approved',
                'attachment_file'     => $attachmentPath,
                'notes'               => $validated['notes'] ?? null,
                'created_by'          => auth()->id() ?? 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            $existingColumns = Schema::getColumnListing('purchase_returns');
            $safeData = array_intersect_key($insertData, array_flip($existingColumns));

            $returnId = DB::table('purchase_returns')->insertGetId($safeData);

            // Insert line items
            $itemCols = Schema::getColumnListing('purchase_return_items');
            foreach ($items as $item) {
                $qty   = (float) ($item['quantity'] ?? 1);
                $cost  = (float) ($item['unit_cost'] ?? 0);
                $tax   = (float) ($item['tax'] ?? 0);
                $total = ($qty * $cost) + $tax;

                $lineData = [
                    'purchase_return_id' => $returnId,
                    'purchase_item_id'   => !empty($item['purchase_item_id']) ? (int)$item['purchase_item_id'] : null,
                    'product_id'         => (int) $item['product_id'],
                    'variant_id'         => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                    'sku'                => $item['sku'] ?? null,
                    'batch_no'           => $item['batch_no'] ?? null,
                    'quantity'           => $qty,
                    'unit_cost'          => $cost,
                    'tax'                => $tax,
                    'total'              => $total,
                    'reason'             => $item['reason'] ?? 'defective',
                    'remarks'            => $item['remarks'] ?? ($item['reason'] ?? 'Defective item returned'),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];

                $safeLineData = array_intersect_key($lineData, array_flip($itemCols));
                DB::table('purchase_return_items')->insert($safeLineData);
            }

            // If immediately approved, deduct inventory and adjust supplier claim
            if ($insertData['status'] === 'approved') {
                $this->processApprovedReturn($returnId);
            }

            DB::commit();
            return redirect()->route('admin.purchase-returns.show', $returnId)
                ->with('success', "Return Note #{$returnNumber} recorded successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to store purchase return: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Purchase Return.
     */
    public function show($id)
    {
        $query = DB::table('purchase_returns')->where('purchase_returns.id', $id);

        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'purchase_returns.supplier_id', '=', 'suppliers.id')
                  ->addSelect(
                      'suppliers.name as supplier_name',
                      'suppliers.company_name as supplier_company',
                      'suppliers.phone as supplier_phone',
                      'suppliers.email as supplier_email',
                      'suppliers.address as supplier_address'
                  );
        }

        if (Schema::hasTable('warehouses')) {
            $query->leftJoin('warehouses', 'purchase_returns.warehouse_id', '=', 'warehouses.id')
                  ->addSelect('warehouses.name as warehouse_name');
        }

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'purchase_returns.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('bank_accounts')) {
            $query->leftJoin('bank_accounts', 'purchase_returns.bank_account_id', '=', 'bank_accounts.id')
                  ->addSelect('bank_accounts.account_name as bank_account_name');
        }

        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchases.purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchases.purchase_no' : 'purchases.id');
            $query->leftJoin('purchases', 'purchase_returns.purchase_id', '=', 'purchases.id')
                  ->addSelect(DB::raw("COALESCE({$numberCol}, purchase_returns.purchase_id) as purchase_number"));
        }

        if (Schema::hasTable('purchase_receipts')) {
            $query->leftJoin('purchase_receipts', 'purchase_returns.purchase_receipt_id', '=', 'purchase_receipts.id')
                  ->addSelect('purchase_receipts.receipt_number as grn_number');
        }

        $query->addSelect('purchase_returns.*');
        $return = $query->first();

        if (!$return) {
            abort(404, 'Purchase return voucher not found.');
        }

        // Fetch line items
        $itemsQuery = DB::table('purchase_return_items')
            ->where('purchase_return_items.purchase_return_id', $id);

        if (Schema::hasTable('products')) {
            $itemsQuery->leftJoin('products', 'purchase_return_items.product_id', '=', 'products.id')
                       ->addSelect('products.title as product_name', 'products.sku as product_sku');
        }

        if (Schema::hasTable('product_variants')) {
            $itemsQuery->leftJoin('product_variants', 'purchase_return_items.variant_id', '=', 'product_variants.id')
                       ->addSelect('product_variants.color as variant_color', 'product_variants.size as variant_size');
        }

        $itemsQuery->addSelect('purchase_return_items.*');
        $items = $itemsQuery->get();

        foreach ($items as $item) {
            $item->product = (object)[
                'name' => $item->product_name ?? ('Product #' . $item->product_id),
                'sku'  => $item->product_sku ?? ($item->sku ?? '—'),
            ];
            $item->variant = !empty($item->variant_id) ? (object)[
                'name' => trim(($item->variant_color ?? '') . ' ' . ($item->variant_size ?? '')),
            ] : null;
        }

        // View Object wrappers
        $return->supplier = (object)[
            'id'           => $return->supplier_id,
            'name'         => $return->supplier_name ?? '—',
            'company_name' => $return->supplier_company ?? '',
            'phone'        => $return->supplier_phone ?? '—',
            'email'        => $return->supplier_email ?? '—',
            'address'      => $return->supplier_address ?? '—',
        ];
        $return->warehouse       = (object)['name' => $return->warehouse_name ?? 'Central Warehouse'];
        $return->branch          = (object)['name' => $return->branch_name ?? 'HQ Branch'];
        $return->bankAccount     = !empty($return->bank_account_name) ? (object)['account_name' => $return->bank_account_name] : null;
        $return->purchase        = (object)['purchase_number' => $return->purchase_number ?? '—'];
        $return->purchaseReceipt = (object)['receipt_number' => $return->grn_number ?? '—'];
        $return->creator         = (object)['name' => 'System Staff'];
        $return->items           = $items;

        return view('admin.procurement.purchase_returns.show', [
            'purchaseReturn' => $return,
            'return'         => $return,
        ]);
    }

    /**
     * Approve return note, deduct physical inventory, and post ledger credit.
     */
    public function approve(Request $request, $id)
    {
        $return = DB::table('purchase_returns')->where('id', $id)->first();
        if (!$return) {
            return redirect()->back()->with('error', 'Return note not found.');
        }

        if ($return->status === 'approved' || $return->status === 'completed') {
            return redirect()->back()->with('warning', 'This return note has already been approved.');
        }

        if ($return->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cancelled return notes cannot be approved.');
        }

        DB::beginTransaction();
        try {
            DB::table('purchase_returns')->where('id', $id)->update([
                'status'     => 'approved',
                'updated_at' => now(),
            ]);

            $this->processApprovedReturn($id);

            DB::commit();
            return redirect()->back()->with('success', "Return Note #{$return->return_number} approved and stock deducted.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel / Void return note and restore physical inventory and supplier balance.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $return = DB::table('purchase_returns')->where('id', $id)->first();
        if (!$return) {
            return redirect()->back()->with('error', 'Return note not found.');
        }

        if ($return->status === 'cancelled') {
            return redirect()->back()->with('warning', 'This return note is already voided.');
        }

        DB::beginTransaction();
        try {
            if ($return->status === 'approved' || $return->status === 'completed') {
                $items = DB::table('purchase_return_items')->where('purchase_return_id', $id)->get();

                // Restore stock
                foreach ($items as $item) {
                    $qty = (float) $item->quantity;
                    DB::table('products')->where('id', $item->product_id)->increment('stock', $qty);
                    if ($item->variant_id && Schema::hasTable('product_variants')) {
                        DB::table('product_variants')->where('id', $item->variant_id)->increment('stock', $qty);
                    }
                }

                // Restore supplier accounts payable balance
                $claimAmount = (float) $return->grand_total;
                if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'current_balance') && $claimAmount > 0) {
                    DB::table('suppliers')->where('id', $return->supplier_id)->increment('current_balance', $claimAmount);
                }

                // Reverse General Ledger entry
                if (Schema::hasTable('account_ledgers')) {
                    DB::table('account_ledgers')->where([
                        'reference_type' => 'purchase_return',
                        'reference_id'   => $id,
                    ])->delete();
                }
            }

            $updateData = [
                'status'     => 'cancelled',
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('purchase_returns', 'cancelled_reason')) {
                $updateData['cancelled_reason'] = $request->cancel_reason;
            }
            if (Schema::hasColumn('purchase_returns', 'notes')) {
                $updateData['notes'] = trim(($return->notes ?? '') . "\n[Void Reason]: " . $request->cancel_reason);
            }

            DB::table('purchase_returns')->where('id', $id)->update($updateData);

            DB::commit();
            return redirect()->back()->with('success', "Return Note #{$return->return_number} has been voided successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Cancellation failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Process stock deduction and supplier balance claim for approved return.
     */
    protected function processApprovedReturn($returnId)
    {
        $return = DB::table('purchase_returns')->where('id', $returnId)->first();
        if (!$return) return;

        $items = DB::table('purchase_return_items')->where('purchase_return_id', $returnId)->get();

        foreach ($items as $item) {
            $qty = (float) $item->quantity;
            if ($qty <= 0) continue;

            // Deduct stock from products
            DB::table('products')->where('id', $item->product_id)->decrement('stock', $qty);
            if ($item->variant_id && Schema::hasTable('product_variants')) {
                DB::table('product_variants')->where('id', $item->variant_id)->decrement('stock', $qty);
            }

            if (Schema::hasTable('inventory_movements')) {
                DB::table('inventory_movements')->insert([
                    'product_id'     => $item->product_id,
                    'variant_id'     => $item->variant_id,
                    'movement_type'  => 'purchase_return',
                    'reference_id'   => $returnId,
                    'quantity_change'=> -$qty,
                    'unit_cost'      => $item->unit_cost ?? 0,
                    'notes'          => 'Purchase Return RTV Outward #' . $return->return_number,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // Deduct Supplier Accounts Payable Liability for the return claim value
        $claimAmount = (float) $return->grand_total;
        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'current_balance') && $claimAmount > 0) {
            DB::table('suppliers')->where('id', $return->supplier_id)->decrement('current_balance', $claimAmount);
        }

        // Record in General Ledger
        if (Schema::hasTable('account_ledgers') && $claimAmount > 0) {
            // Dr: Accounts Payable (Reduces Liability)
            DB::table('account_ledgers')->insert([
                'chart_of_account_id' => $return->supplier_id,
                'reference_type'      => 'purchase_return',
                'reference_id'        => $returnId,
                'reference_number'    => $return->return_number,
                'date'                => $return->return_date,
                'debit'               => $claimAmount,
                'credit'              => 0,
                'description'         => 'Purchase Return RTV Liability Deduction #' . $return->return_number,
                'branch_id'           => $return->branch_id ?? null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // Cr: Inventory Outflow / Purchase Return Head
            DB::table('account_ledgers')->insert([
                'chart_of_account_id' => 1,
                'reference_type'      => 'purchase_return',
                'reference_id'        => $returnId,
                'reference_number'    => $return->return_number,
                'date'                => $return->return_date,
                'debit'               => 0,
                'credit'              => $claimAmount,
                'description'         => 'Inventory Outflow Reversal on Return #' . $return->return_number,
                'branch_id'           => $return->branch_id ?? null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }
}
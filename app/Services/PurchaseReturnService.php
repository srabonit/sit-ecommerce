<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseReturnService
{
    protected SupplierLedgerService $ledgerService;

    public function __construct(SupplierLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Create a Purchase Return Note, deduct physical stock,
     * record outward inventory movements, and update supplier liability.
     *
     * @param array $data [
     *     'supplier_id', 'purchase_id', 'branch_id', 'warehouse_id',
     *     'return_date', 'return_reason', 'settlement_type', 'supplier_reference_no',
     *     'attachment_file', 'notes', 'auto_approve',
     *     'items' => [
     *         [
     *             'purchase_item_id', 'product_id', 'variant_id', 'sku', 'barcode',
     *             'batch_no', 'return_quantity', 'unit_cost', 'tax_amount', 'item_reason'
     *         ]
     *     ]
     * ]
     * @return PurchaseReturn
     * @throws Exception
     */
    public function createReturn(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['items']) || !is_array($data['items'])) {
                throw new InvalidArgumentException('Purchase return must contain at least one line item.');
            }

            $subtotal = 0.00;
            $totalTax = 0.00;
            $preparedItems = [];

            foreach ($data['items'] as $item) {
                $qty = (float) ($item['return_quantity'] ?? 0);
                $unitCost = (float) ($item['unit_cost'] ?? 0);
                $tax = (float) ($item['tax_amount'] ?? 0);

                if ($qty <= 0) {
                    throw new InvalidArgumentException('Return quantity must be greater than zero for all items.');
                }

                $lineSubtotal = round($qty * $unitCost, 2);
                $lineTotal = round($lineSubtotal + $tax, 2);

                $subtotal += $lineSubtotal;
                $totalTax += $tax;

                $preparedItems[] = [
                    'purchase_item_id' => $item['purchase_item_id'] ?? null,
                    'product_id'       => $item['product_id'],
                    'variant_id'       => $item['variant_id'] ?? null,
                    'sku'              => $item['sku'] ?? null,
                    'barcode'          => $item['barcode'] ?? null,
                    'batch_no'         => $item['batch_no'] ?? null,
                    'return_quantity'  => $qty,
                    'unit_cost'        => $unitCost,
                    'tax_amount'       => $tax,
                    'total_cost'       => $lineTotal,
                    'item_reason'      => $item['item_reason'] ?? null,
                ];
            }

            $totalReturnAmount = round($subtotal + $totalTax, 2);
            $autoApprove = $data['auto_approve'] ?? true;
            $authUserId = Auth::id();

            $returnRecord = PurchaseReturn::create([
                'return_number'         => $this->generateReturnNumber(),
                'supplier_id'           => $data['supplier_id'],
                'purchase_id'           => $data['purchase_id'] ?? null,
                'branch_id'             => $data['branch_id'] ?? null,
                'warehouse_id'          => $data['warehouse_id'] ?? null,
                'return_date'           => $data['return_date'] ?? now()->toDateString(),
                'return_reason'         => $data['return_reason'] ?? 'defective',
                'status'                => $autoApprove ? 'approved' : 'pending_approval',
                'settlement_type'       => $data['settlement_type'] ?? 'bill_adjustment',
                'subtotal'              => $subtotal,
                'tax_amount'            => $totalTax,
                'total_return_amount'   => $totalReturnAmount,
                'refunded_amount'       => 0.00,
                'adjusted_amount'       => 0.00,
                'supplier_reference_no' => $data['supplier_reference_no'] ?? null,
                'attachment_file'       => $data['attachment_file'] ?? null,
                'created_by'            => $authUserId ?? ($data['created_by'] ?? null),
                'approved_by'           => $autoApprove ? ($authUserId ?? ($data['approved_by'] ?? null)) : null,
                'approved_at'           => $autoApprove ? now() : null,
                'notes'                 => $data['notes'] ?? null,
            ]);

            foreach ($preparedItems as $itemData) {
                $returnItem = $returnRecord->items()->create($itemData);

                // If approved immediately, adjust inventory stock
                if ($autoApprove) {
                    $this->deductStockAndRecordMovement(
                        returnRecord: $returnRecord,
                        returnItem: $returnItem,
                        returnQty: (int) $itemData['return_quantity'],
                        unitCost: (float) $itemData['unit_cost'],
                        authUserId: $authUserId
                    );
                }
            }

            if ($autoApprove) {
                $this->processFinancialSettlement($returnRecord);
            }

            return $returnRecord->load(['items', 'supplier']);
        });
    }

    /**
     * Approve a pending Purchase Return and execute inventory deduction + financial ledger sync.
     *
     * @param PurchaseReturn $returnRecord
     * @param int|null $approvedBy
     * @return PurchaseReturn
     * @throws Exception
     */
    public function approveReturn(PurchaseReturn $returnRecord, ?int $approvedBy = null): PurchaseReturn
    {
        return DB::transaction(function () use ($returnRecord, $approvedBy) {
            $lockedReturn = PurchaseReturn::with('items')->where('id', $returnRecord->id)->lockForUpdate()->firstOrFail();

            if ($lockedReturn->status === 'approved') {
                throw new Exception('Purchase return is already approved.');
            }

            $authUserId = $approvedBy ?? Auth::id();

            // Deduct stock for each line item
            foreach ($lockedReturn->items as $item) {
                $this->deductStockAndRecordMovement(
                    returnRecord: $lockedReturn,
                    returnItem: $item,
                    returnQty: (int) $item->return_quantity,
                    unitCost: (float) $item->unit_cost,
                    authUserId: $authUserId
                );
            }

            $lockedReturn->update([
                'status'      => 'approved',
                'approved_by' => $authUserId,
                'approved_at' => now(),
            ]);

            $this->processFinancialSettlement($lockedReturn);

            return $lockedReturn;
        });
    }

    /**
     * Deduct physical stock on Product / Variant and log an outward inventory movement.
     */
    protected function deductStockAndRecordMovement(
        PurchaseReturn $returnRecord,
        PurchaseReturnItem $returnItem,
        int $returnQty,
        float $unitCost,
        ?int $authUserId
    ): void {
        $variant = null;
        if (!empty($returnItem->variant_id)) {
            $variant = ProductVariant::where('id', $returnItem->variant_id)->lockForUpdate()->first();
        }

        $product = Product::where('id', $returnItem->product_id)->lockForUpdate()->firstOrFail();

        $qtyBefore = $variant ? (int) ($variant->stock_quantity ?? 0) : (int) ($product->stock_quantity ?? 0);
        $qtyAfter = max(0, $qtyBefore - $returnQty);

        if ($variant) {
            $variant->stock_quantity = $qtyAfter;
            $variant->save();

            if (isset($product->stock_quantity)) {
                $product->decrement('stock_quantity', $returnQty);
            }
        } else {
            $product->stock_quantity = $qtyAfter;
            $product->save();
        }

        // Create immutable Outward Inventory Movement Ledger
        InventoryMovement::create([
            'product_id'          => $product->id,
            'variant_id'          => $variant?->id,
            'sku'                 => $returnItem->sku ?? ($variant?->sku ?? $product->sku),
            'movement_type'       => 'supplier_return',
            'quantity_before'     => $qtyBefore,
            'quantity_change'     => -$returnQty,
            'quantity_after'      => $qtyAfter,
            'unit_cost'           => $unitCost,
            'unit_price'          => (float) ($variant?->price ?? $product->price ?? 0.00),
            'reference_id'        => $returnRecord->id,
            'source'              => 'procurement_return',
            'created_by'          => $authUserId,
            'notes'               => "Goods returned under Return Note #{$returnRecord->return_number}",

            // Procurement References
            'supplier_id'         => $returnRecord->supplier_id,
            'purchase_id'         => $returnRecord->purchase_id,
            'purchase_item_id'    => $returnItem->purchase_item_id,
            'purchase_return_id'  => $returnRecord->id,
            'branch_id'           => $returnRecord->branch_id,
            'warehouse_id'        => $returnRecord->warehouse_id,
            'batch_no'            => $returnItem->batch_no,
        ]);
    }

    /**
     * Handle financial impact: Supplier Ledger debit and linked Purchase bill adjustments.
     */
    protected function processFinancialSettlement(PurchaseReturn $returnRecord): void
    {
        // 1. Post Debit Entry to Supplier Ledger (Reduces balance owed to supplier)
        $this->ledgerService->recordReturn($returnRecord);

        // 2. Adjust linked purchase invoice if applicable
        if ($returnRecord->settlement_type === 'bill_adjustment' && !empty($returnRecord->purchase_id)) {
            $purchase = Purchase::where('id', $returnRecord->purchase_id)->lockForUpdate()->first();

            if ($purchase && (float) $purchase->due_amount > 0) {
                $returnTotal = (float) $returnRecord->total_return_amount;
                $currentDue = (float) $purchase->due_amount;

                $adjustmentAmount = min($returnTotal, $currentDue);
                $newDue = round($currentDue - $adjustmentAmount, 2);

                $purchase->due_amount = $newDue;
                if ($newDue <= 0.00) {
                    $purchase->payment_status = 'paid';
                }
                $purchase->save();

                $returnRecord->update([
                    'adjusted_amount' => $adjustmentAmount,
                ]);
            }
        }
    }

    /**
     * Generate unique sequential Return Note number: PRET-YYYYMMDD-XXXX
     */
    protected function generateReturnNumber(): string
    {
        $prefix = 'PRET-' . date('Ymd') . '-';
        $lastReturn = PurchaseReturn::where('return_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastReturn) {
            $parts = explode('-', $lastReturn->return_number);
            $seq = intval(end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseReceiptService
{
    /**
     * Process a Goods Received Note (GRN), inspect items, update order fulfillments,
     * and automatically post inventory movements with pessimistic locking.
     *
     * @param array $data [
     *     'purchase_order_id', 'purchase_id', 'supplier_id', 'branch_id', 'warehouse_id',
     *     'supplier_challan_no', 'receiving_date', 'received_by', 'inspected_by', 'inspection_notes',
     *     'attachment_file',
     *     'items' => [
     *         [
     *             'purchase_order_item_id', 'purchase_item_id', 'product_id', 'variant_id',
     *             'sku', 'barcode', 'batch_no', 'expiry_date', 'ordered_quantity',
     *             'received_quantity', 'accepted_quantity', 'rejected_quantity',
     *             'rejection_reason', 'unit_cost', 'remarks'
     *         ]
     *     ]
     * ]
     * @return PurchaseReceipt
     * @throws Exception
     */
    public function createReceipt(array $data): PurchaseReceipt
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['items']) || !is_array($data['items'])) {
                throw new InvalidArgumentException('Receipt note must contain at least one line item.');
            }

            $receiptNumber = $this->generateReceiptNumber();
            $authUserId = Auth::id();

            // Determine overall receiving status
            $totalReceived = 0.0;
            $totalAccepted = 0.0;
            $totalRejected = 0.0;

            foreach ($data['items'] as $item) {
                $totalReceived += (float) ($item['received_quantity'] ?? 0);
                $totalAccepted += (float) ($item['accepted_quantity'] ?? 0);
                $totalRejected += (float) ($item['rejected_quantity'] ?? 0);
            }

            if ($totalReceived <= 0) {
                throw new InvalidArgumentException('Total received quantity across all items must be greater than zero.');
            }

            $receivingStatus = 'accepted';
            if ($totalRejected > 0 && $totalAccepted > 0) {
                $receivingStatus = 'partially_accepted';
            } elseif ($totalAccepted <= 0 && $totalRejected > 0) {
                $receivingStatus = 'rejected';
            }

            // Create Master Goods Received Note (GRN)
            $receipt = PurchaseReceipt::create([
                'receipt_number'      => $receiptNumber,
                'purchase_order_id'   => $data['purchase_order_id'] ?? null,
                'purchase_id'         => $data['purchase_id'] ?? null,
                'supplier_id'         => $data['supplier_id'],
                'branch_id'           => $data['branch_id'] ?? null,
                'warehouse_id'        => $data['warehouse_id'] ?? null,
                'supplier_challan_no' => $data['supplier_challan_no'] ?? null,
                'receiving_date'      => $data['receiving_date'] ?? now()->toDateString(),
                'receiving_status'    => $receivingStatus,
                'received_by'         => $data['received_by'] ?? $authUserId,
                'inspected_by'        => $data['inspected_by'] ?? $authUserId,
                'inspection_notes'    => $data['inspection_notes'] ?? null,
                'attachment_file'     => $data['attachment_file'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $orderedQty = (float) ($itemData['ordered_quantity'] ?? 0);
                $receivedQty = (float) ($itemData['received_quantity'] ?? 0);
                $acceptedQty = (float) ($itemData['accepted_quantity'] ?? 0);
                $rejectedQty = (float) ($itemData['rejected_quantity'] ?? 0);
                $unitCost = (float) ($itemData['unit_cost'] ?? 0);
                $totalCost = round($acceptedQty * $unitCost, 2);

                $receiptItem = $receipt->items()->create([
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'] ?? null,
                    'purchase_item_id'       => $itemData['purchase_item_id'] ?? null,
                    'product_id'             => $itemData['product_id'],
                    'variant_id'             => $itemData['variant_id'] ?? null,
                    'sku'                    => $itemData['sku'] ?? null,
                    'barcode'                => $itemData['barcode'] ?? null,
                    'batch_no'               => $itemData['batch_no'] ?? null,
                    'expiry_date'            => $itemData['expiry_date'] ?? null,
                    'ordered_quantity'       => $orderedQty,
                    'received_quantity'      => $receivedQty,
                    'accepted_quantity'      => $acceptedQty,
                    'rejected_quantity'      => $rejectedQty,
                    'rejection_reason'       => $itemData['rejection_reason'] ?? null,
                    'unit_cost'              => $unitCost,
                    'total_cost'             => $totalCost,
                    'remarks'                => $itemData['remarks'] ?? null,
                ]);

                // Update PO Item received progress if applicable
                if (!empty($itemData['purchase_order_item_id'])) {
                    $poItem = PurchaseOrderItem::where('id', $itemData['purchase_order_item_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($poItem) {
                        $poItem->received_quantity = round((float) $poItem->received_quantity + $acceptedQty, 2);
                        $poItem->save();
                    }
                }

                // Increment Inventory Stock & Post Inventory Movement for Accepted Qty
                if ($acceptedQty > 0) {
                    $this->adjustStockAndRecordMovement(
                        receipt: $receipt,
                        receiptItem: $receiptItem,
                        acceptedQty: (int) $acceptedQty,
                        unitCost: $unitCost,
                        authUserId: $authUserId
                    );
                }
            }

            // Synchronize linked Purchase Order status
            if (!empty($data['purchase_order_id'])) {
                $this->syncPoCompletionStatus((int) $data['purchase_order_id']);
            }

            // Synchronize linked Purchase Invoice status
            if (!empty($data['purchase_id'])) {
                $this->syncPurchaseStatus((int) $data['purchase_id']);
            }

            return $receipt->load(['items', 'supplier', 'inventoryMovements']);
        });
    }

    /**
     * Atomically increment Product / Variant physical stock and record InventoryMovement.
     */
    protected function adjustStockAndRecordMovement(
        PurchaseReceipt $receipt,
        PurchaseReceiptItem $receiptItem,
        int $acceptedQty,
        float $unitCost,
        ?int $authUserId
    ): void {
        $variant = null;
        if (!empty($receiptItem->variant_id)) {
            $variant = ProductVariant::where('id', $receiptItem->variant_id)->lockForUpdate()->first();
        }

        $product = Product::where('id', $receiptItem->product_id)->lockForUpdate()->firstOrFail();

        // Baseline quantity tracking before increment
        $qtyBefore = $variant ? (int) ($variant->stock_quantity ?? 0) : (int) ($product->stock_quantity ?? 0);
        $qtyAfter = $qtyBefore + $acceptedQty;

        // Update physical stock balance on product/variant
        if ($variant) {
            $variant->stock_quantity = $qtyAfter;
            $variant->save();

            // Also increment aggregate parent stock if maintained
            if (isset($product->stock_quantity)) {
                $product->increment('stock_quantity', $acceptedQty);
            }
        } else {
            $product->stock_quantity = $qtyAfter;
            $product->save();
        }

        // Create immutable Inventory Movement Ledger entry
        InventoryMovement::create([
            'product_id'          => $product->id,
            'variant_id'          => $variant?->id,
            'sku'                 => $receiptItem->sku ?? ($variant?->sku ?? $product->sku),
            'movement_type'       => 'purchase_receive',
            'quantity_before'     => $qtyBefore,
            'quantity_change'     => $acceptedQty,
            'quantity_after'      => $qtyAfter,
            'unit_cost'           => $unitCost,
            'unit_price'          => (float) ($variant?->price ?? $product->price ?? 0.00),
            'reference_id'        => $receipt->id,
            'source'              => 'procurement_grn',
            'created_by'          => $authUserId,
            'notes'               => "Goods received under GRN #{$receipt->receipt_number}",

            // Phase 1 Enhanced Foreign Keys
            'supplier_id'         => $receipt->supplier_id,
            'purchase_id'         => $receipt->purchase_id,
            'purchase_item_id'    => $receiptItem->purchase_item_id,
            'purchase_receipt_id' => $receipt->id,
            'branch_id'           => $receipt->branch_id,
            'warehouse_id'        => $receipt->warehouse_id,
            'receiving_reference' => $receipt->receipt_number,
            'batch_no'            => $receiptItem->batch_no,
            'expiry_date'         => $receiptItem->expiry_date,
        ]);
    }

    /**
     * Check and update status on PurchaseOrder.
     */
    protected function syncPoCompletionStatus(int $purchaseOrderId): void
    {
        $po = PurchaseOrder::with('items')->where('id', $purchaseOrderId)->lockForUpdate()->first();
        if (!$po) {
            return;
        }

        $allFulfilled = true;
        $anyReceived = false;

        foreach ($po->items as $item) {
            $received = (float) $item->received_quantity;
            $ordered = (float) $item->quantity;

            if ($received > 0) {
                $anyReceived = true;
            }
            if ($received < $ordered) {
                $allFulfilled = false;
            }
        }

        if ($allFulfilled) {
            $po->status = 'completed';
        } elseif ($anyReceived) {
            $po->status = 'partially_received';
        }
        $po->save();
    }

    /**
     * Check and update status on Purchase Invoice.
     */
    protected function syncPurchaseStatus(int $purchaseId): void
    {
        $purchase = Purchase::where('id', $purchaseId)->lockForUpdate()->first();
        if ($purchase && $purchase->purchase_status !== 'received') {
            $purchase->purchase_status = 'received';
            $purchase->receiving_date = now()->toDateString();
            $purchase->save();
        }
    }

    /**
     * Generate unique sequential GRN number: GRN-YYYYMMDD-XXXX
     */
    protected function generateReceiptNumber(): string
    {
        $prefix = 'GRN-' . date('Ymd') . '-';
        $lastReceipt = PurchaseReceipt::where('receipt_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastReceipt) {
            $parts = explode('-', $lastReceipt->receipt_number);
            $seq = intval(end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
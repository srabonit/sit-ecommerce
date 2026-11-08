<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    protected SupplierLedgerService $ledgerService;

    public function __construct(SupplierLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Create a new Purchase Order with line items.
     *
     * @param array $data [
     *     'supplier_id', 'branch_id', 'warehouse_id', 'order_date', 'expected_delivery_date',
     *     'reference_no', 'discount_amount', 'tax_amount', 'shipping_cost', 'terms_conditions', 'notes',
     *     'items' => [
     *         ['product_id', 'variant_id', 'sku', 'barcode', 'quantity', 'unit_cost', 'discount_amount', 'tax_amount']
     *     ]
     * ]
     * @return PurchaseOrder
     * @throws Exception
     */
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['items']) || !is_array($data['items'])) {
                throw new InvalidArgumentException('Purchase order must contain at least one line item.');
            }

            $subtotal = 0.00;
            $itemsPayload = [];

            foreach ($data['items'] as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $unitCost = (float) ($item['unit_cost'] ?? 0);
                $discount = (float) ($item['discount_amount'] ?? 0);
                $tax = (float) ($item['tax_amount'] ?? 0);

                if ($qty <= 0) {
                    throw new InvalidArgumentException('Quantity must be greater than zero for all items.');
                }

                $lineSubtotal = round($qty * $unitCost, 2);
                $lineTotal = round($lineSubtotal - $discount + $tax, 2);
                $subtotal += $lineSubtotal;

                $itemsPayload[] = [
                    'product_id'        => $item['product_id'],
                    'variant_id'        => $item['variant_id'] ?? null,
                    'sku'               => $item['sku'] ?? null,
                    'barcode'           => $item['barcode'] ?? null,
                    'quantity'          => $qty,
                    'unit_cost'         => $unitCost,
                    'discount_amount'   => $discount,
                    'tax_amount'        => $tax,
                    'total_cost'        => $lineTotal,
                    'received_quantity' => 0.00,
                ];
            }

            $orderDiscount = (float) ($data['discount_amount'] ?? 0.00);
            $orderTax = (float) ($data['tax_amount'] ?? 0.00);
            $shippingCost = (float) ($data['shipping_cost'] ?? 0.00);
            $grandTotal = round($subtotal - $orderDiscount + $orderTax + $shippingCost, 2);

            $po = PurchaseOrder::create([
                'po_number'              => $this->generatePoNumber(),
                'supplier_id'            => $data['supplier_id'],
                'branch_id'              => $data['branch_id'] ?? null,
                'warehouse_id'           => $data['warehouse_id'] ?? null,
                'order_date'             => $data['order_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'reference_no'           => $data['reference_no'] ?? null,
                'subtotal'               => $subtotal,
                'discount_amount'        => $orderDiscount,
                'tax_amount'             => $orderTax,
                'shipping_cost'          => $shippingCost,
                'total_amount'           => $grandTotal,
                'status'                 => $data['status'] ?? 'pending_approval',
                'created_by'             => Auth::id() ?? ($data['created_by'] ?? null),
                'terms_conditions'       => $data['terms_conditions'] ?? null,
                'notes'                  => $data['notes'] ?? null,
            ]);

            foreach ($itemsPayload as $itemData) {
                $po->items()->create($itemData);
            }

            return $po->load(['items', 'supplier']);
        });
    }

    /**
     * Approve a Purchase Order.
     */
    public function approvePurchaseOrder(PurchaseOrder $po, ?int $approvedBy = null): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $approvedBy) {
            $lockedPo = PurchaseOrder::where('id', $po->id)->lockForUpdate()->firstOrFail();

            if (!in_array($lockedPo->status, ['draft', 'pending_approval'])) {
                throw new Exception("Purchase Order cannot be approved from current status: {$lockedPo->status}");
            }

            $lockedPo->update([
                'status'      => 'approved',
                'approved_by' => $approvedBy ?? Auth::id(),
                'approved_at' => now(),
            ]);

            return $lockedPo;
        });
    }

    /**
     * Cancel a Purchase Order.
     */
    public function cancelPurchaseOrder(PurchaseOrder $po, ?int $cancelledBy = null, ?string $reason = null): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $cancelledBy, $reason) {
            $lockedPo = PurchaseOrder::where('id', $po->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedPo->status, ['completed', 'cancelled'])) {
                throw new Exception("Cannot cancel an order that is already {$lockedPo->status}.");
            }

            $receivedQty = (float) $lockedPo->items()->sum('received_quantity');
            if ($receivedQty > 0) {
                throw new Exception('Cannot cancel a PO that has already received items. Consider closing or returning instead.');
            }

            $lockedPo->update([
                'status'       => 'cancelled',
                'cancelled_by' => $cancelledBy ?? Auth::id(),
                'cancelled_at' => now(),
                'notes'        => $reason ? ($lockedPo->notes . "\n[Cancellation Reason]: " . $reason) : $lockedPo->notes,
            ]);

            return $lockedPo;
        });
    }

    /**
     * Create a Purchase Bill / Invoice (Direct or from Purchase Order)
     * and automatically synchronize with the Supplier Ledger.
     *
     * @param array $data [
     *     'purchase_order_id', 'supplier_id', 'branch_id', 'warehouse_id',
     *     'supplier_invoice_no', 'supplier_challan_no', 'purchase_date', 'receiving_date',
     *     'purchase_type', 'discount_amount', 'tax_amount', 'shipping_cost', 'other_charges',
     *     'purchase_status', 'notes',
     *     'items' => [
     *         ['purchase_order_item_id', 'product_id', 'variant_id', 'sku', 'barcode', 'batch_no',
     *          'expiry_date', 'quantity', 'unit_cost', 'discount_amount', 'tax_amount']
     *     ]
     * ]
     * @return Purchase
     * @throws Exception
     */
    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['items']) || !is_array($data['items'])) {
                throw new InvalidArgumentException('Purchase bill must contain at least one item line.');
            }

            $subtotal = 0.00;
            $preparedItems = [];

            foreach ($data['items'] as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $unitCost = (float) ($item['unit_cost'] ?? 0);
                $discount = (float) ($item['discount_amount'] ?? 0);
                $tax = (float) ($item['tax_amount'] ?? 0);

                if ($qty <= 0) {
                    throw new InvalidArgumentException('Quantity must be greater than zero for all purchase items.');
                }

                $lineSubtotal = round($qty * $unitCost, 2);
                $lineTotal = round($lineSubtotal - $discount + $tax, 2);
                $subtotal += $lineSubtotal;

                $preparedItems[] = [
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_id'             => $item['product_id'],
                    'variant_id'             => $item['variant_id'] ?? null,
                    'sku'                    => $item['sku'] ?? null,
                    'barcode'                => $item['barcode'] ?? null,
                    'batch_no'               => $item['batch_no'] ?? null,
                    'expiry_date'            => $item['expiry_date'] ?? null,
                    'quantity'               => $qty,
                    'unit_cost'              => $unitCost,
                    'unit_cost_snapshot'     => $unitCost,
                    'discount_amount'        => $discount,
                    'tax_amount'             => $tax,
                    'subtotal'               => $lineSubtotal,
                    'total_cost'             => $lineTotal,
                    'landed_cost_share'      => 0.00,
                ];
            }

            $orderDiscount = (float) ($data['discount_amount'] ?? 0.00);
            $orderTax = (float) ($data['tax_amount'] ?? 0.00);
            $shippingCost = (float) ($data['shipping_cost'] ?? 0.00);
            $otherCharges = (float) ($data['other_charges'] ?? 0.00);

            $grandTotal = round($subtotal - $orderDiscount + $orderTax + $shippingCost + $otherCharges, 2);
            $totalAdditionalLanded = $shippingCost + $otherCharges;

            // Distribute landed costs proportionally across items based on subtotal share
            if ($totalAdditionalLanded > 0 && $subtotal > 0) {
                foreach ($preparedItems as &$pItem) {
                    $ratio = $pItem['subtotal'] / $subtotal;
                    $pItem['landed_cost_share'] = round($totalAdditionalLanded * $ratio, 2);
                }
                unset($pItem);
            }

            $purchase = Purchase::create([
                'purchase_number'     => $this->generatePurchaseNumber(),
                'purchase_order_id'   => $data['purchase_order_id'] ?? null,
                'supplier_id'         => $data['supplier_id'],
                'branch_id'           => $data['branch_id'] ?? null,
                'warehouse_id'        => $data['warehouse_id'] ?? null,
                'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
                'supplier_challan_no' => $data['supplier_challan_no'] ?? null,
                'purchase_date'       => $data['purchase_date'] ?? now()->toDateString(),
                'receiving_date'      => $data['receiving_date'] ?? null,
                'purchase_type'       => $data['purchase_type'] ?? 'inventory',
                'subtotal'            => $subtotal,
                'discount_amount'     => $orderDiscount,
                'tax_amount'          => $orderTax,
                'shipping_cost'       => $shippingCost,
                'other_charges'       => $otherCharges,
                'grand_total'         => $grandTotal,
                'paid_amount'         => 0.00,
                'due_amount'          => $grandTotal,
                'purchase_status'     => $data['purchase_status'] ?? 'pending',
                'payment_status'      => 'unpaid',
                'created_by'          => Auth::id() ?? ($data['created_by'] ?? null),
                'approved_by'         => $data['approved_by'] ?? null,
                'notes'               => $data['notes'] ?? null,
            ]);

            foreach ($preparedItems as $itemData) {
                $purchase->items()->create($itemData);
            }

            // Sync with Supplier Ledger (Posts credit liability entry)
            $this->ledgerService->recordPurchase($purchase);

            // Update linked Purchase Order status if applicable
            if (!empty($data['purchase_order_id'])) {
                $this->syncPoStatus($data['purchase_order_id']);
            }

            return $purchase->load(['items', 'supplier']);
        });
    }

    /**
     * Recalculate and update Purchase Order fulfillment status.
     */
    protected function syncPoStatus(int $purchaseOrderId): void
    {
        $po = PurchaseOrder::with('items')->find($purchaseOrderId);
        if (!$po) {
            return;
        }

        $allFulfilled = true;
        $anyReceived = false;

        foreach ($po->items as $item) {
            if ((float) $item->received_quantity > 0) {
                $anyReceived = true;
            }
            if ((float) $item->received_quantity < (float) $item->quantity) {
                $allFulfilled = false;
            }
        }

        if ($allFulfilled) {
            $po->update(['status' => 'completed']);
        } elseif ($anyReceived) {
            $po->update(['status' => 'partially_received']);
        }
    }

    /**
     * Generate unique sequential PO number: PO-YYYYMMDD-XXXX
     */
    protected function generatePoNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $lastPo = PurchaseOrder::where('po_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastPo) {
            $parts = explode('-', $lastPo->po_number);
            $seq = intval(end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique sequential Purchase number: PUR-YYYYMMDD-XXXX
     */
    protected function generatePurchaseNumber(): string
    {
        $prefix = 'PUR-' . date('Ymd') . '-';
        $lastPurchase = Purchase::where('purchase_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastPurchase) {
            $parts = explode('-', $lastPurchase->purchase_number);
            $seq = intval(end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
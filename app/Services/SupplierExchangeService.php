<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SupplierDebitCreditNote;
use App\Models\SupplierExchange;
use App\Models\SupplierExchangeItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SupplierExchangeService
{
    protected SupplierLedgerService $ledgerService;

    public function __construct(SupplierLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Create a Supplier Exchange record with both outward return items and inward replacement items.
     *
     * @param array $data [
     *     'supplier_id', 'purchase_id', 'purchase_return_id', 'branch_id', 'warehouse_id',
     *     'exchange_date', 'difference_settlement', 'notes', 'auto_approve',
     *     'returned_items' => [
     *         ['product_id', 'variant_id', 'sku', 'barcode', 'batch_no', 'quantity', 'unit_cost', 'remarks']
     *     ],
     *     'replacement_items' => [
     *         ['product_id', 'variant_id', 'sku', 'barcode', 'batch_no', 'quantity', 'unit_cost', 'remarks']
     *     ]
     * ]
     * @return SupplierExchange
     * @throws Exception
     */
    public function createExchange(array $data): SupplierExchange
    {
        return DB::transaction(function () use ($data) {
            $returnedItems = $data['returned_items'] ?? [];
            $replacementItems = $data['replacement_items'] ?? [];

            if (empty($returnedItems) || empty($replacementItems)) {
                throw new InvalidArgumentException('An exchange requires at least one returned item and one replacement item.');
            }

            $returnedTotalValue = 0.00;
            $preparedReturned = [];
            foreach ($returnedItems as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $unitCost = (float) ($item['unit_cost'] ?? 0);

                if ($qty <= 0) {
                    throw new InvalidArgumentException('Quantity must be greater than zero for all returned items.');
                }

                $lineTotal = round($qty * $unitCost, 2);
                $returnedTotalValue += $lineTotal;

                $preparedReturned[] = [
                    'item_type'   => 'returned_out',
                    'product_id'  => $item['product_id'],
                    'variant_id'  => $item['variant_id'] ?? null,
                    'sku'         => $item['sku'] ?? null,
                    'barcode'     => $item['barcode'] ?? null,
                    'batch_no'    => $item['batch_no'] ?? null,
                    'quantity'    => $qty,
                    'unit_cost'   => $unitCost,
                    'total_cost'  => $lineTotal,
                    'remarks'     => $item['remarks'] ?? null,
                ];
            }

            $replacementTotalValue = 0.00;
            $preparedReplacement = [];
            foreach ($replacementItems as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $unitCost = (float) ($item['unit_cost'] ?? 0);

                if ($qty <= 0) {
                    throw new InvalidArgumentException('Quantity must be greater than zero for all replacement items.');
                }

                $lineTotal = round($qty * $unitCost, 2);
                $replacementTotalValue += $lineTotal;

                $preparedReplacement[] = [
                    'item_type'   => 'replacement_in',
                    'product_id'  => $item['product_id'],
                    'variant_id'  => $item['variant_id'] ?? null,
                    'sku'         => $item['sku'] ?? null,
                    'barcode'     => $item['barcode'] ?? null,
                    'batch_no'    => $item['batch_no'] ?? null,
                    'quantity'    => $qty,
                    'unit_cost'   => $unitCost,
                    'total_cost'  => $lineTotal,
                    'remarks'     => $item['remarks'] ?? null,
                ];
            }

            // Difference = Replacement Total - Returned Total
            // If > 0: Company owes supplier additional funds (Payable)
            // If < 0: Supplier owes company credit/refund (Receivable)
            $priceDifference = round($replacementTotalValue - $returnedTotalValue, 2);
            $autoApprove = $data['auto_approve'] ?? false;
            $authUserId = Auth::id();

            $exchange = SupplierExchange::create([
                'exchange_number'         => $this->generateExchangeNumber(),
                'supplier_id'             => $data['supplier_id'],
                'purchase_id'             => $data['purchase_id'] ?? null,
                'purchase_return_id'      => $data['purchase_return_id'] ?? null,
                'branch_id'               => $data['branch_id'] ?? null,
                'warehouse_id'            => $data['warehouse_id'] ?? null,
                'exchange_date'           => $data['exchange_date'] ?? now()->toDateString(),
                'status'                  => $autoApprove ? 'completed' : 'requested',
                'returned_total_value'    => $returnedTotalValue,
                'replacement_total_value' => $replacementTotalValue,
                'price_difference'        => $priceDifference,
                'difference_settlement'   => $data['difference_settlement'] ?? 'adjust_ledger',
                'created_by'              => $authUserId ?? ($data['created_by'] ?? null),
                'approved_by'             => $autoApprove ? ($authUserId ?? ($data['approved_by'] ?? null)) : null,
                'approved_at'             => $autoApprove ? now() : null,
                'notes'                   => $data['notes'] ?? null,
            ]);

            // Save outward and inward item lines
            foreach (array_merge($preparedReturned, $preparedReplacement) as $itemData) {
                $exchange->items()->create($itemData);
            }

            if ($autoApprove) {
                $this->executeStockAndLedgerSync($exchange, $authUserId);
            }

            return $exchange->load(['items', 'supplier']);
        });
    }

    /**
     * Approve and finalize an exchange: execute stock outward/inward movements
     * and post price differences to the Supplier Ledger.
     *
     * @param SupplierExchange $exchange
     * @param int|null $approvedBy
     * @return SupplierExchange
     * @throws Exception
     */
    public function approveExchange(SupplierExchange $exchange, ?int $approvedBy = null): SupplierExchange
    {
        return DB::transaction(function () use ($exchange, $approvedBy) {
            $lockedExchange = SupplierExchange::with('items')->where('id', $exchange->id)->lockForUpdate()->firstOrFail();

            if ($lockedExchange->status === 'completed') {
                throw new Exception('Exchange is already completed.');
            }

            $authUserId = $approvedBy ?? Auth::id();

            $this->executeStockAndLedgerSync($lockedExchange, $authUserId);

            $lockedExchange->update([
                'status'      => 'completed',
                'approved_by' => $authUserId,
                'approved_at' => now(),
            ]);

            return $lockedExchange;
        });
    }

    /**
     * Execute inventory movements (deduct returned, add replacement) and ledger settlement.
     */
    protected function executeStockAndLedgerSync(SupplierExchange $exchange, ?int $authUserId): void
    {
        foreach ($exchange->items as $item) {
            if ($item->item_type === 'returned_out') {
                $this->deductStockMovement($exchange, $item, $authUserId);
            } elseif ($item->item_type === 'replacement_in') {
                $this->addStockMovement($exchange, $item, $authUserId);
            }
        }

        // Handle financial impact if price difference exists
        $priceDiff = (float) $exchange->price_difference;
        if (abs($priceDiff) > 0.00) {
            $this->generateNoteAndLedgerEntry($exchange, $priceDiff, $authUserId);
        }
    }

    /**
     * Outward stock deduction for returned item.
     */
    protected function deductStockMovement(SupplierExchange $exchange, SupplierExchangeItem $item, ?int $authUserId): void
    {
        $variant = $item->variant_id ? ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first() : null;
        $product = Product::where('id', $item->product_id)->lockForUpdate()->firstOrFail();

        $qtyBefore = $variant ? (int) ($variant->stock_quantity ?? 0) : (int) ($product->stock_quantity ?? 0);
        $qtyChange = (int) $item->quantity;
        $qtyAfter = max(0, $qtyBefore - $qtyChange);

        if ($variant) {
            $variant->stock_quantity = $qtyAfter;
            $variant->save();

            if (isset($product->stock_quantity)) {
                $product->decrement('stock_quantity', $qtyChange);
            }
        } else {
            $product->stock_quantity = $qtyAfter;
            $product->save();
        }

        InventoryMovement::create([
            'product_id'           => $product->id,
            'variant_id'           => $variant?->id,
            'sku'                  => $item->sku ?? ($variant?->sku ?? $product->sku),
            'movement_type'        => 'supplier_exchange_out',
            'quantity_before'      => $qtyBefore,
            'quantity_change'      => -$qtyChange,
            'quantity_after'       => $qtyAfter,
            'unit_cost'            => (float) $item->unit_cost,
            'unit_price'           => (float) ($variant?->price ?? $product->price ?? 0.00),
            'reference_id'         => $exchange->id,
            'source'               => 'procurement_exchange_out',
            'created_by'           => $authUserId,
            'notes'                => "Exchange return outward under #{$exchange->exchange_number}",
            'supplier_id'          => $exchange->supplier_id,
            'purchase_id'          => $exchange->purchase_id,
            'supplier_exchange_id' => $exchange->id,
            'branch_id'            => $exchange->branch_id,
            'warehouse_id'         => $exchange->warehouse_id,
            'batch_no'             => $item->batch_no,
        ]);
    }

    /**
     * Inward stock addition for replacement item.
     */
    protected function addStockMovement(SupplierExchange $exchange, SupplierExchangeItem $item, ?int $authUserId): void
    {
        $variant = $item->variant_id ? ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first() : null;
        $product = Product::where('id', $item->product_id)->lockForUpdate()->firstOrFail();

        $qtyBefore = $variant ? (int) ($variant->stock_quantity ?? 0) : (int) ($product->stock_quantity ?? 0);
        $qtyChange = (int) $item->quantity;
        $qtyAfter = $qtyBefore + $qtyChange;

        if ($variant) {
            $variant->stock_quantity = $qtyAfter;
            $variant->save();

            if (isset($product->stock_quantity)) {
                $product->increment('stock_quantity', $qtyChange);
            }
        } else {
            $product->stock_quantity = $qtyAfter;
            $product->save();
        }

        InventoryMovement::create([
            'product_id'           => $product->id,
            'variant_id'           => $variant?->id,
            'sku'                  => $item->sku ?? ($variant?->sku ?? $product->sku),
            'movement_type'        => 'supplier_exchange_in',
            'quantity_before'      => $qtyBefore,
            'quantity_change'      => $qtyChange,
            'quantity_after'       => $qtyAfter,
            'unit_cost'            => (float) $item->unit_cost,
            'unit_price'           => (float) ($variant?->price ?? $product->price ?? 0.00),
            'reference_id'         => $exchange->id,
            'source'               => 'procurement_exchange_in',
            'created_by'           => $authUserId,
            'notes'                => "Replacement item received under exchange #{$exchange->exchange_number}",
            'supplier_id'          => $exchange->supplier_id,
            'purchase_id'          => $exchange->purchase_id,
            'supplier_exchange_id' => $exchange->id,
            'branch_id'            => $exchange->branch_id,
            'warehouse_id'         => $exchange->warehouse_id,
            'batch_no'             => $item->batch_no,
        ]);
    }

    /**
     * Post price difference to Supplier Debit/Credit Note and sync with ledger.
     */
    protected function generateNoteAndLedgerEntry(SupplierExchange $exchange, float $difference, ?int $authUserId): void
    {
        $isPayableDifference = $difference > 0;
        $amount = abs($difference);

        // Note type: credit_note if we owe more; debit_note if supplier owes us
        $noteType = $isPayableDifference ? 'credit_note' : 'debit_note';
        $prefix = $isPayableDifference ? 'SCN-' : 'SDN-';

        $noteNumber = $prefix . date('Ymd') . '-' . str_pad($exchange->id, 4, '0', STR_PAD_LEFT);

        $note = SupplierDebitCreditNote::create([
            'note_number'          => $noteNumber,
            'supplier_id'          => $exchange->supplier_id,
            'purchase_id'          => $exchange->purchase_id,
            'supplier_exchange_id' => $exchange->id,
            'note_type'            => $noteType,
            'reason_type'          => 'exchange_difference',
            'note_date'            => $exchange->exchange_date,
            'amount'               => $amount,
            'tax_amount'           => 0.00,
            'total_amount'         => $amount,
            'status'               => 'approved',
            'created_by'           => $authUserId,
            'approved_by'          => $authUserId,
            'description'          => "Valuation difference for exchange #{$exchange->exchange_number}",
        ]);

        // Post ledger entry directly via ledger service
        $this->ledgerService->recordDebitCreditNote($note);
    }

    /**
     * Generate unique sequential Exchange number: EXCH-YYYYMMDD-XXXX
     */
    protected function generateExchangeNumber(): string
    {
        $prefix = 'EXCH-' . date('Ymd') . '-';
        $lastExchange = SupplierExchange::where('exchange_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastExchange) {
            $parts = explode('-', $lastExchange->exchange_number);
            $seq = intval(end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class InventoryService
{
    /**
     * Atomically deduct stock for either a product variant or a simple product.
     * Prevents negative stock / overselling via row-level database locking (lockForUpdate).
     * Automatically captures the purchase cost snapshot for order items and logs movement.
     *
     * @throws Exception
     */
    public static function deductStock(
        int $productId,
        ?int $variantId,
        int $quantity,
        string $movementType,
        string $referenceId,
        ?float $unitPrice = null,
        string $source = 'web_checkout',
        ?int $userId = null,
        ?string $notes = null,
        ?string $idempotencyToken = null
    ): array {
        if ($quantity <= 0) {
            throw new Exception("Invalid deduction quantity: {$quantity}. Quantity must be greater than zero.");
        }

        // Idempotency check: prevent duplicate stock deduction
        if (!empty($idempotencyToken) && Schema::hasTable('inventory_movements') && Schema::hasColumn('inventory_movements', 'idempotency_token')) {
            $existing = DB::table('inventory_movements')->where('idempotency_token', $idempotencyToken)->first();
            if ($existing) {
                return [
                    'success'           => true,
                    'idempotent_replay' => true,
                    'message'           => 'Movement already processed with this token.',
                    'product_id'        => $existing->product_id,
                    'variant_id'        => $existing->variant_id,
                    'quantity_deducted' => abs($existing->quantity_change),
                    'stock_remaining'   => $existing->quantity_after,
                    'purchase_cost'     => (float) $existing->unit_cost,
                    'cogs'              => round((float) $existing->unit_cost * abs($existing->quantity_change), 2),
                ];
            }
        }

        $execute = function () use ($productId, $variantId, $quantity, $movementType, $referenceId, $unitPrice, $source, $userId, $notes, $idempotencyToken) {
            $isCostEstimated = false;
            $purchaseCost = 0.00;
            $sku = null;
            $qtyBefore = 0;
            $qtyAfter = 0;

            // =========================================================================
            // CASE 1: Variable Product (Variant ID provided)
            // =========================================================================
            if (!empty($variantId)) {
                $variant = DB::table('product_variants')
                    ->where('id', $variantId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new Exception("Product variant #{$variantId} for Product #{$productId} was not found.");
                }

                $sku = $variant->product_code ?? ($variant->sku ?? ('VAR-' . $variant->id));
                $qtyBefore = (int) ($variant->stock ?? 0);

                if ($qtyBefore < $quantity) {
                    $variantTitle = trim(($variant->color ?? '') . ' ' . ($variant->size ?? ''));
                    $label = !empty($variantTitle) ? "({$variantTitle})" : "SKU: {$sku}";
                    throw new Exception("Insufficient stock for variant {$label}. Available: {$qtyBefore}, Requested: {$quantity}.");
                }

                $qtyAfter = $qtyBefore - $quantity;

                // 1. Deduct variant stock
                DB::table('product_variants')
                    ->where('id', $variantId)
                    ->update([
                        'stock'      => $qtyAfter,
                        'updated_at' => now(),
                    ]);

                // 2. Sync parent product stock (Parent Stock = Sum of all variants)
                self::syncParentProductStock($productId);

                // 3. Resolve Purchase Cost Snapshot
                $rawVariantCost = $variant->purchase_cost ?? ($variant->purchase_price ?? null);
                if (!is_null($rawVariantCost) && (float) $rawVariantCost > 0) {
                    $purchaseCost = (float) $rawVariantCost;
                } else {
                    $parentCost = null;
                    if (Schema::hasColumn('products', 'purchase_cost')) {
                        $parentCost = DB::table('products')->where('id', $productId)->value('purchase_cost');
                    }
                    if (empty($parentCost) && Schema::hasColumn('products', 'purchase_price')) {
                        $parentCost = DB::table('products')->where('id', $productId)->value('purchase_price');
                    }

                    if (!empty($parentCost) && (float) $parentCost > 0) {
                        $purchaseCost = (float) $parentCost;
                        $isCostEstimated = true;
                    } else {
                        $purchaseCost = 0.00;
                        $isCostEstimated = true;
                    }
                }
            }
            // =========================================================================
            // CASE 2: Simple Product (No Variant ID)
            // =========================================================================
            else {
                if (Schema::hasTable('product_variants')) {
                    $hasVariants = DB::table('product_variants')->where('product_id', $productId)->exists();
                    if ($hasVariants) {
                        throw new Exception("Product #{$productId} is a variable product. A specific variant must be selected.");
                    }
                }

                $product = DB::table('products')
                    ->where('id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw new Exception("Product #{$productId} not found.");
                }

                $sku = $product->sku ?? ($product->product_code ?? ('PRD-' . $product->id));
                $qtyBefore = (int) ($product->stock ?? 0);

                if ($qtyBefore < $quantity) {
                    throw new Exception("Insufficient stock for product '{$product->title}'. Available: {$qtyBefore}, Requested: {$quantity}.");
                }

                $qtyAfter = $qtyBefore - $quantity;

                DB::table('products')
                    ->where('id', $productId)
                    ->update([
                        'stock'      => $qtyAfter,
                        'updated_at' => now(),
                    ]);

                $rawProductCost = $product->purchase_cost ?? ($product->purchase_price ?? null);
                if (!is_null($rawProductCost) && (float) $rawProductCost > 0) {
                    $purchaseCost = (float) $rawProductCost;
                } else {
                    $purchaseCost = 0.00;
                    $isCostEstimated = true;
                }
            }

            // =========================================================================
            // AUDIT: Record Inventory Movement Log
            // =========================================================================
            self::logMovement([
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'sku'               => $sku,
                'movement_type'     => $movementType,
                'quantity_before'   => $qtyBefore,
                'quantity_change'   => -$quantity,
                'quantity_after'    => $qtyAfter,
                'unit_cost'         => $purchaseCost,
                'unit_price'        => $unitPrice,
                'reference_id'      => $referenceId,
                'source'            => $source,
                'idempotency_token' => $idempotencyToken,
                'created_by'        => $userId ?? auth()->id(),
                'notes'             => $notes ?? "Stock deducted via {$source} for Ref #{$referenceId}",
            ]);

            return [
                'success'           => true,
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'sku'               => $sku,
                'quantity_deducted' => $quantity,
                'quantity_before'   => $qtyBefore,
                'stock_remaining'   => $qtyAfter,
                'purchase_cost'     => round($purchaseCost, 2),
                'cogs'              => round($purchaseCost * $quantity, 2),
                'is_cost_estimated' => $isCostEstimated,
            ];
        };

        return DB::transactionLevel() > 0 ? $execute() : DB::transaction($execute);
    }

    // =========================================================================
    // ENTERPRISE ORDER ESTIMATION & PROCESSING WORKFLOW METHODS
    // =========================================================================

    /**
     * Increment Estimated Order Quantity without altering actual physical stock.
     */
    public static function recordOrderEstimate(int $productId, ?int $variantId, int $quantity, string $referenceId = ''): void
    {
        if ($quantity <= 0) return;

        DB::transaction(function () use ($productId, $variantId, $quantity) {
            if (!empty($variantId) && Schema::hasTable('product_variants')) {
                if (Schema::hasColumn('product_variants', 'estimated_order_qty')) {
                    DB::table('product_variants')
                        ->where('id', $variantId)
                        ->increment('estimated_order_qty', $quantity);
                }
                self::syncParentProductStock($productId);
            } else {
                if (Schema::hasColumn('products', 'estimated_order_qty')) {
                    DB::table('products')
                        ->where('id', $productId)
                        ->increment('estimated_order_qty', $quantity);
                }
            }
        });
    }

    /**
     * Decrement Estimated Order Quantity safely.
     */
    public static function releaseOrderEstimate(int $productId, ?int $variantId, int $quantity, string $referenceId = ''): void
    {
        if ($quantity <= 0) return;

        DB::transaction(function () use ($productId, $variantId, $quantity) {
            if (!empty($variantId) && Schema::hasTable('product_variants')) {
                if (Schema::hasColumn('product_variants', 'estimated_order_qty')) {
                    $current = (int) DB::table('product_variants')->where('id', $variantId)->value('estimated_order_qty');
                    $newVal = max(0, $current - $quantity);
                    DB::table('product_variants')->where('id', $variantId)->update(['estimated_order_qty' => $newVal]);
                }
                self::syncParentProductStock($productId);
            } else {
                if (Schema::hasColumn('products', 'estimated_order_qty')) {
                    $current = (int) DB::table('products')->where('id', $productId)->value('estimated_order_qty');
                    $newVal = max(0, $current - $quantity);
                    DB::table('products')->where('id', $productId)->update(['estimated_order_qty' => $newVal]);
                }
            }
        });
    }

    /**
     * Transition Order to 'processing' (Packed Stage)
     */
    public static function deductOrderStockAtProcessing(object|array $order, ?int $userId = null): array
    {
        $orderObj = is_array($order) ? (object) $order : $order;
        $orderId = $orderObj->id;
        $invoiceNo = $orderObj->invoice_no ?? ($orderObj->order_number ?? "ORD-{$orderId}");

        if (!empty($orderObj->is_stock_deducted)) {
            return [
                'success' => true,
                'already_deducted' => true,
                'message' => "Stock for order #{$invoiceNo} was already deducted.",
            ];
        }

        return DB::transaction(function () use ($orderId, $invoiceNo, $userId) {
            $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';
            $items = DB::table($itemsTable)->where('order_id', $orderId)->get();

            if ($items->isEmpty()) {
                throw new Exception("No items found for Order #{$invoiceNo}.");
            }

            $deductions = [];

            foreach ($items as $item) {
                $pId = (int) $item->product_id;
                $vId = !empty($item->variant_id) ? (int) $item->variant_id : null;
                $qty = (int) ($item->quantity ?? ($item->qty ?? 1));
                $unitPrice = (float) ($item->unit_price ?? ($item->price ?? 0.00));
                $idempotencyToken = "ORD-DEDUCT-{$orderId}-{$item->id}";

                self::releaseOrderEstimate($pId, $vId, $qty, $invoiceNo);

                $result = self::deductStock(
                    $pId,
                    $vId,
                    $qty,
                    'order_processing_deduct',
                    $invoiceNo,
                    $unitPrice,
                    'order_processing',
                    $userId,
                    "Physical stock deducted upon order packing #{$invoiceNo}",
                    $idempotencyToken
                );

                $updateFields = [];
                if (Schema::hasColumn($itemsTable, 'purchase_cost') && empty($item->purchase_cost)) {
                    $updateFields['purchase_cost'] = $result['purchase_cost'];
                }
                if (Schema::hasColumn($itemsTable, 'cogs') && empty($item->cogs)) {
                    $updateFields['cogs'] = $result['cogs'];
                }
                if (!empty($updateFields)) {
                    DB::table($itemsTable)->where('id', $item->id)->update($updateFields);
                }

                $deductions[] = $result;
            }

            DB::table('orders')->where('id', $orderId)->update([
                'is_stock_deducted' => true,
                'is_stock_restored' => false,
                'updated_at'        => now(),
            ]);

            return [
                'success'    => true,
                'order_id'   => $orderId,
                'invoice_no' => $invoiceNo,
                'deductions' => $deductions,
            ];
        });
    }

    /**
     * Cancel an Order cleanly
     */
    public static function cancelOrderStock(object|array $order, ?int $userId = null, ?string $reason = 'Order cancelled'): array
    {
        $orderObj = is_array($order) ? (object) $order : $order;
        $orderId = $orderObj->id;
        $invoiceNo = $orderObj->invoice_no ?? ($orderObj->order_number ?? "ORD-{$orderId}");

        return DB::transaction(function () use ($orderObj, $orderId, $invoiceNo, $userId, $reason) {
            $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';
            $items = DB::table($itemsTable)->where('order_id', $orderId)->get();

            $wasDeducted = (bool) ($orderObj->is_stock_deducted ?? false);
            $alreadyRestored = (bool) ($orderObj->is_stock_restored ?? false);

            if ($alreadyRestored) {
                return ['success' => true, 'message' => 'Stock was already restored for this order.'];
            }

            foreach ($items as $item) {
                $pId = (int) $item->product_id;
                $vId = !empty($item->variant_id) ? (int) $item->variant_id : null;
                $qty = (int) ($item->quantity ?? ($item->qty ?? 1));

                if ($wasDeducted) {
                    self::restoreStock(
                        $pId,
                        $vId,
                        $qty,
                        'order_cancel',
                        $invoiceNo,
                        'order_cancel',
                        $userId,
                        "Stock restored on cancel for Order #{$invoiceNo}: {$reason}",
                        "ORD-RESTORE-CANCEL-{$orderId}-{$item->id}"
                    );
                } else {
                    self::releaseOrderEstimate($pId, $vId, $qty, $invoiceNo);
                }
            }

            DB::table('orders')->where('id', $orderId)->update([
                'is_stock_restored' => true,
                'updated_at'        => now(),
            ]);

            return [
                'success'       => true,
                'order_id'      => $orderId,
                'physical_restored' => $wasDeducted,
                'message'       => $wasDeducted ? 'Physical stock restored successfully.' : 'Estimated reservation released.',
            ];
        });
    }

    /**
     * Process Returned Order with Sellable vs Damaged Inspection
     */
    public static function processOrderReturn(
        object|array $order,
        array $inspectedItems = [],
        ?int $userId = null,
        ?string $notes = null
    ): array {
        $orderObj = is_array($order) ? (object) $order : $order;
        $orderId = $orderObj->id;
        $invoiceNo = $orderObj->invoice_no ?? ($orderObj->order_number ?? "ORD-{$orderId}");

        return DB::transaction(function () use ($orderObj, $orderId, $invoiceNo, $inspectedItems, $userId, $notes) {
            $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';
            $items = DB::table($itemsTable)->where('order_id', $orderId)->get();

            $wasDeducted = (bool) ($orderObj->is_stock_deducted ?? false);
            $alreadyRestored = (bool) ($orderObj->is_stock_restored ?? false);

            if ($alreadyRestored) {
                return ['success' => true, 'message' => 'Return stock was already processed for this order.'];
            }

            $restockedItems = [];

            foreach ($items as $item) {
                $pId = (int) $item->product_id;
                $vId = !empty($item->variant_id) ? (int) $item->variant_id : null;
                $qty = (int) ($item->quantity ?? ($item->qty ?? 1));

                $isSellable = true;
                if (isset($inspectedItems[$item->id])) {
                    $isSellable = (bool) ($inspectedItems[$item->id]['is_sellable'] ?? true);
                }

                if ($wasDeducted) {
                    if ($isSellable) {
                        $res = self::restoreStock(
                            $pId,
                            $vId,
                            $qty,
                            'order_return',
                            $invoiceNo,
                            'order_return_sellable',
                            $userId,
                            "Sellable stock restored on return #{$invoiceNo}. " . ($notes ?? ''),
                            "RET-SELLABLE-{$orderId}-{$item->id}"
                        );
                        $restockedItems[] = ['item_id' => $item->id, 'status' => 'sellable_restocked', 'qty' => $qty];
                    } else {
                        $unitCost = (float) ($item->purchase_cost ?? 0.00);
                        self::recordDamagedReturn(
                            $pId,
                            $vId,
                            $qty,
                            $invoiceNo,
                            $unitCost,
                            $userId,
                            "Returned item damaged / unsellable for Order #{$invoiceNo}. Loss recorded."
                        );
                        $restockedItems[] = ['item_id' => $item->id, 'status' => 'damaged_loss_logged', 'qty' => $qty];
                    }
                } else {
                    self::releaseOrderEstimate($pId, $vId, $qty, $invoiceNo);
                    $restockedItems[] = ['item_id' => $item->id, 'status' => 'estimate_released', 'qty' => $qty];
                }
            }

            DB::table('orders')->where('id', $orderId)->update([
                'is_stock_restored' => true,
                'updated_at'        => now(),
            ]);

            return [
                'success'    => true,
                'order_id'   => $orderId,
                'items'      => $restockedItems,
                'message'    => 'Order return stock reconciliation completed cleanly.',
            ];
        });
    }

    /**
     * Dedicated Stock In Method
     */
    public static function stockIn(
        int $productId,
        ?int $variantId,
        int $quantity,
        ?float $purchaseCost = null,
        string $movementType = 'purchase_in',
        ?string $referenceId = null,
        string $source = 'stock_in_form',
        ?float $sellingPrice = null,
        ?int $supplierId = null,
        ?int $userId = null,
        ?string $notes = null,
        bool $updateWac = true,
        ?string $idempotencyToken = null
    ): array {
        if ($quantity <= 0) {
            throw new Exception("Invalid inbound quantity: {$quantity}. Quantity must be greater than zero.");
        }

        if (!empty($idempotencyToken) && Schema::hasTable('inventory_movements') && Schema::hasColumn('inventory_movements', 'idempotency_token')) {
            $existing = DB::table('inventory_movements')->where('idempotency_token', $idempotencyToken)->first();
            if ($existing) {
                return [
                    'success'           => true,
                    'idempotent_replay' => true,
                    'message'           => 'Stock In already processed with this token.',
                    'product_id'        => $existing->product_id,
                    'variant_id'        => $existing->variant_id,
                    'quantity_added'    => $existing->quantity_change,
                    'stock_total'       => $existing->quantity_after,
                    'unit_cost'         => (float) $existing->unit_cost,
                ];
            }
        }

        $execute = function () use ($productId, $variantId, $quantity, $purchaseCost, $movementType, $referenceId, $source, $sellingPrice, $supplierId, $userId, $notes, $updateWac, $idempotencyToken) {
            $sku = null;
            $qtyBefore = 0;
            $qtyAfter = 0;
            $finalCost = 0.00;

            if (!empty($variantId)) {
                $variant = DB::table('product_variants')
                    ->where('id', $variantId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new Exception("Variant #{$variantId} for Product #{$productId} not found.");
                }

                $sku = $variant->product_code ?? ($variant->sku ?? ('VAR-' . $variant->id));
                $qtyBefore = max(0, (int) ($variant->stock ?? 0));
                $currentCost = (float) ($variant->purchase_cost ?? ($variant->purchase_price ?? 0.00));
                $qtyAfter = $qtyBefore + $quantity;

                if (!is_null($purchaseCost) && $purchaseCost > 0) {
                    $finalCost = $updateWac 
                        ? self::calculateWAC($qtyBefore, $currentCost, $quantity, (float) $purchaseCost)
                        : (float) $purchaseCost;
                } else {
                    $finalCost = $currentCost;
                }

                $updateData = [
                    'stock'         => $qtyAfter,
                    'purchase_cost' => round($finalCost, 2),
                    'updated_at'    => now(),
                ];

                if (!is_null($sellingPrice) && $sellingPrice > 0 && Schema::hasColumn('product_variants', 'regular_price')) {
                    $updateData['regular_price'] = round($sellingPrice, 2);
                }

                DB::table('product_variants')->where('id', $variantId)->update($updateData);

                self::syncParentProductStock($productId);
            } else {
                if (Schema::hasTable('product_variants')) {
                    $hasVariants = DB::table('product_variants')->where('product_id', $productId)->exists();
                    if ($hasVariants) {
                        throw new Exception("Product #{$productId} is a variable product. You must select a specific variant to add stock.");
                    }
                }

                $product = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
                if (!$product) {
                    throw new Exception("Product #{$productId} not found.");
                }

                $sku = $product->sku ?? ($product->product_code ?? ('PRD-' . $product->id));
                $qtyBefore = max(0, (int) ($product->stock ?? 0));
                $currentCost = (float) ($product->purchase_cost ?? ($product->purchase_price ?? 0.00));
                $qtyAfter = $qtyBefore + $quantity;

                if (!is_null($purchaseCost) && $purchaseCost > 0) {
                    $finalCost = $updateWac 
                        ? self::calculateWAC($qtyBefore, $currentCost, $quantity, (float) $purchaseCost)
                        : (float) $purchaseCost;
                } else {
                    $finalCost = $currentCost;
                }

                $updateData = [
                    'stock'         => $qtyAfter,
                    'purchase_cost' => round($finalCost, 2),
                    'updated_at'    => now(),
                ];

                if (Schema::hasColumn('products', 'purchase_price')) {
                    $updateData['purchase_price'] = round($finalCost, 2);
                }

                if (!is_null($sellingPrice) && $sellingPrice > 0) {
                    $updateData['regular_price'] = round($sellingPrice, 2);
                }

                DB::table('products')->where('id', $productId)->update($updateData);
            }

            self::logMovement([
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'sku'               => $sku,
                'movement_type'     => $movementType,
                'quantity_before'   => $qtyBefore,
                'quantity_change'   => +$quantity,
                'quantity_after'    => $qtyAfter,
                'unit_cost'         => round($finalCost, 2),
                'unit_price'        => $sellingPrice,
                'reference_id'      => $referenceId,
                'source'            => $source,
                'idempotency_token' => $idempotencyToken,
                'created_by'        => $userId ?? auth()->id(),
                'notes'             => $notes ?? "Stock In (+{$quantity}) via {$source}" . ($supplierId ? " [Supplier #{$supplierId}]" : ""),
            ]);

            return [
                'success'         => true,
                'product_id'      => $productId,
                'variant_id'      => $variantId,
                'sku'             => $sku,
                'quantity_added'  => $quantity,
                'quantity_before' => $qtyBefore,
                'stock_total'     => $qtyAfter,
                'purchase_cost'   => round($finalCost, 2),
                'total_valuation' => round($qtyAfter * $finalCost, 2),
            ];
        };

        return DB::transactionLevel() > 0 ? $execute() : DB::transaction($execute);
    }

    /**
     * Dedicated Stock Out Method
     */
    public static function stockOut(
        int $productId,
        ?int $variantId,
        int $quantity,
        string $movementType = 'damage_out',
        ?string $referenceId = null,
        string $source = 'stock_out_form',
        ?int $userId = null,
        ?string $notes = null,
        ?string $idempotencyToken = null
    ): array {
        if ($quantity <= 0) {
            throw new Exception("Invalid outbound quantity: {$quantity}. Must be greater than zero.");
        }

        if (!empty($idempotencyToken) && Schema::hasTable('inventory_movements') && Schema::hasColumn('inventory_movements', 'idempotency_token')) {
            $existing = DB::table('inventory_movements')->where('idempotency_token', $idempotencyToken)->first();
            if ($existing) {
                return [
                    'success'           => true,
                    'idempotent_replay' => true,
                    'message'           => 'Stock Out already processed with this token.',
                    'product_id'        => $existing->product_id,
                    'variant_id'        => $existing->variant_id,
                    'quantity_removed'  => abs($existing->quantity_change),
                    'stock_remaining'   => $existing->quantity_after,
                ];
            }
        }

        $execute = function () use ($productId, $variantId, $quantity, $movementType, $referenceId, $source, $userId, $notes, $idempotencyToken) {
            $sku = null;
            $unitCost = 0.00;
            $qtyBefore = 0;
            $qtyAfter = 0;

            if (!empty($variantId)) {
                $variant = DB::table('product_variants')
                    ->where('id', $variantId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new Exception("Variant #{$variantId} for Product #{$productId} not found.");
                }

                $sku = $variant->product_code ?? ($variant->sku ?? ('VAR-' . $variant->id));
                $qtyBefore = (int) ($variant->stock ?? 0);

                if ($qtyBefore < $quantity) {
                    throw new Exception("Insufficient stock for variant (Available: {$qtyBefore}, Requested removal: {$quantity}).");
                }

                $unitCost = (float) ($variant->purchase_cost ?? ($variant->purchase_price ?? 0.00));
                $qtyAfter = $qtyBefore - $quantity;

                DB::table('product_variants')
                    ->where('id', $variantId)
                    ->update([
                        'stock'      => $qtyAfter,
                        'updated_at' => now(),
                    ]);

                self::syncParentProductStock($productId);
            } else {
                $product = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
                if (!$product) {
                    throw new Exception("Product #{$productId} not found.");
                }

                $sku = $product->sku ?? ($product->product_code ?? ('PRD-' . $product->id));
                $qtyBefore = (int) ($product->stock ?? 0);

                if ($qtyBefore < $quantity) {
                    throw new Exception("Insufficient stock for product '{$product->title}' (Available: {$qtyBefore}, Requested removal: {$quantity}).");
                }

                $unitCost = (float) ($product->purchase_cost ?? ($product->purchase_price ?? 0.00));
                $qtyAfter = $qtyBefore - $quantity;

                DB::table('products')
                    ->where('id', $productId)
                    ->update([
                        'stock'      => $qtyAfter,
                        'updated_at' => now(),
                    ]);
            }

            self::logMovement([
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'sku'               => $sku,
                'movement_type'     => $movementType,
                'quantity_before'   => $qtyBefore,
                'quantity_change'   => -$quantity,
                'quantity_after'    => $qtyAfter,
                'unit_cost'         => $unitCost,
                'unit_price'        => null,
                'reference_id'      => $referenceId,
                'source'            => $source,
                'idempotency_token' => $idempotencyToken,
                'created_by'        => $userId ?? auth()->id(),
                'notes'             => $notes ?? "Non-sale Stock Out (-{$quantity}) via {$source}",
            ]);

            return [
                'success'          => true,
                'product_id'       => $productId,
                'variant_id'       => $variantId,
                'sku'              => $sku,
                'quantity_removed' => $quantity,
                'quantity_before'  => $qtyBefore,
                'stock_remaining'  => $qtyAfter,
                'unit_cost'        => round($unitCost, 2),
                'cost_impact'      => round($unitCost * $quantity, 2),
            ];
        };

        return DB::transactionLevel() > 0 ? $execute() : DB::transaction($execute);
    }

    /**
     * Atomically restore/refund stock for an item (Order Cancel, Return, or Void).
     */
    public static function restoreStock(
        int $productId,
        ?int $variantId,
        int $quantity,
        string $movementType,
        string $referenceId,
        string $source = 'order_cancel',
        ?int $userId = null,
        ?string $notes = null,
        ?string $idempotencyToken = null
    ): array {
        if ($quantity <= 0) {
            throw new Exception("Invalid restore quantity: {$quantity}. Must be greater than zero.");
        }

        if (!empty($idempotencyToken) && Schema::hasTable('inventory_movements') && Schema::hasColumn('inventory_movements', 'idempotency_token')) {
            $existing = DB::table('inventory_movements')->where('idempotency_token', $idempotencyToken)->first();
            if ($existing) {
                return [
                    'success'           => true,
                    'idempotent_replay' => true,
                    'message'           => 'Stock restore already processed with this token.',
                    'product_id'        => $existing->product_id,
                    'variant_id'        => $existing->variant_id,
                    'quantity_added'    => $existing->quantity_change,
                    'stock_total'       => $existing->quantity_after,
                ];
            }
        }

        $execute = function () use ($productId, $variantId, $quantity, $movementType, $referenceId, $source, $userId, $notes, $idempotencyToken) {
            $sku = null;
            $unitCost = 0.00;
            $qtyBefore = 0;
            $qtyAfter = 0;

            if (!empty($variantId)) {
                $variant = DB::table('product_variants')
                    ->where('id', $variantId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new Exception("Variant #{$variantId} for Product #{$productId} not found.");
                }

                $sku = $variant->product_code ?? ($variant->sku ?? ('VAR-' . $variant->id));
                $unitCost = (float) ($variant->purchase_cost ?? ($variant->purchase_price ?? 0.00));
                $qtyBefore = (int) ($variant->stock ?? 0);
                $qtyAfter = $qtyBefore + $quantity;

                DB::table('product_variants')
                    ->where('id', $variantId)
                    ->update([
                        'stock'      => $qtyAfter,
                        'updated_at' => now(),
                    ]);

                self::syncParentProductStock($productId);
            } else {
                $product = DB::table('products')
                    ->where('id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw new Exception("Product #{$productId} not found.");
                }

                $sku = $product->sku ?? ($product->product_code ?? ('PRD-' . $product->id));
                $unitCost = (float) ($product->purchase_cost ?? ($product->purchase_price ?? 0.00));
                $qtyBefore = (int) ($product->stock ?? 0);
                $qtyAfter = $qtyBefore + $quantity;

                DB::table('products')
                    ->where('id', $productId)
                    ->update([
                        'stock'      => $qtyAfter,
                        'updated_at' => now(),
                    ]);
            }

            self::logMovement([
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'sku'               => $sku,
                'movement_type'     => $movementType,
                'quantity_before'   => $qtyBefore,
                'quantity_change'   => +$quantity,
                'quantity_after'    => $qtyAfter,
                'unit_cost'         => $unitCost,
                'unit_price'        => null,
                'reference_id'      => $referenceId,
                'source'            => $source,
                'idempotency_token' => $idempotencyToken,
                'created_by'        => $userId ?? auth()->id(),
                'notes'             => $notes ?? "Stock restored via {$source} for Ref #{$referenceId}",
            ]);

            return [
                'success'         => true,
                'product_id'      => $productId,
                'variant_id'      => $variantId,
                'sku'             => $sku,
                'quantity_added'  => $quantity,
                'quantity_before' => $qtyBefore,
                'stock_total'     => $qtyAfter,
            ];
        };

        return DB::transactionLevel() > 0 ? $execute() : DB::transaction($execute);
    }

    /**
     * Universal addStock method / alias for return restocking
     */
    public static function addStock(
        int $productId,
        ?int $variantId,
        int $quantity,
        string $movementType = 'return_restock',
        string $referenceId = '',
        ?float $unitCost = null,
        string $source = 'order_return',
        ?int $userId = null,
        ?string $notes = null,
        ?string $idempotencyToken = null
    ): array {
        return self::restoreStock(
            $productId,
            $variantId,
            $quantity,
            $movementType,
            $referenceId,
            $source,
            $userId,
            $notes,
            $idempotencyToken
        );
    }

    /**
     * Record a damaged item returned from an order without adding it back to sellable stock.
     * Uses row object property fallback to eliminate SQL 'Column not found' errors.
     */
    public static function recordDamagedReturn(
        int $productId,
        ?int $variantId,
        int $quantity,
        string $referenceId,
        ?float $unitCost = null,
        ?int $userId = null,
        ?string $notes = null
    ): void {
        $sku = null;
        if (!empty($variantId) && Schema::hasTable('product_variants')) {
            $vRow = DB::table('product_variants')->where('id', $variantId)->first();
            if ($vRow) {
                $sku = $vRow->product_code ?? ($vRow->sku ?? ('VAR-' . $variantId));
            }
        }
        if (empty($sku) && Schema::hasTable('products')) {
            $pRow = DB::table('products')->where('id', $productId)->first();
            if ($pRow) {
                $sku = $pRow->sku ?? ($pRow->product_code ?? ('PRD-' . $productId));
            }
        }

        self::logMovement([
            'product_id'        => $productId,
            'variant_id'        => $variantId,
            'sku'               => $sku ?? '',
            'movement_type'     => 'damage_out',
            'quantity_before'   => 0,
            'quantity_change'   => -$quantity,
            'quantity_after'    => 0,
            'unit_cost'         => (float) ($unitCost ?? 0.00),
            'unit_price'        => null,
            'reference_id'      => $referenceId,
            'source'            => 'order_return_damaged',
            'idempotency_token' => "RET-DAMAGED-{$referenceId}-{$productId}-" . ($variantId ?? 0) . "-" . time(),
            'created_by'        => $userId ?? auth()->id(),
            'notes'             => $notes ?? "Order #{$referenceId} item returned damaged: Scrapped/Write-off ({$quantity} pcs)",
        ]);
    }

    /**
     * Stock adjustment or periodic reconciliation
     */
    public static function adjustStock(
        int $productId,
        ?int $variantId,
        int $newStock,
        ?float $newCost = null,
        bool $applyWac = false,
        string $movementType = 'adjustment',
        ?string $referenceId = null,
        string $source = 'manual_adjustment',
        ?int $userId = null,
        ?string $notes = null,
        ?string $idempotencyToken = null
    ): array {
        if ($newStock < 0) {
            throw new Exception("Stock cannot be negative.");
        }

        $execute = function () use ($productId, $variantId, $newStock, $newCost, $applyWac, $movementType, $referenceId, $source, $userId, $notes, $idempotencyToken) {
            $sku = null;
            $qtyBefore = 0;
            $finalCost = 0.00;

            if (!empty($variantId)) {
                $variant = DB::table('product_variants')
                    ->where('id', $variantId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new Exception("Variant #{$variantId} not found.");
                }

                $sku = $variant->product_code ?? ($variant->sku ?? ('VAR-' . $variant->id));
                $qtyBefore = (int) ($variant->stock ?? 0);
                $currentCost = (float) ($variant->purchase_cost ?? ($variant->purchase_price ?? 0.00));

                if (!is_null($newCost) && $applyWac && $newStock > $qtyBefore) {
                    $incomingQty = $newStock - $qtyBefore;
                    $finalCost = self::calculateWAC($qtyBefore, $currentCost, $incomingQty, $newCost);
                } elseif (!is_null($newCost)) {
                    $finalCost = $newCost;
                } else {
                    $finalCost = $currentCost;
                }

                $updateData = [
                    'stock'         => $newStock,
                    'purchase_cost' => round($finalCost, 2),
                    'updated_at'    => now(),
                ];

                DB::table('product_variants')->where('id', $variantId)->update($updateData);
                self::syncParentProductStock($productId);
            } else {
                $product = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
                if (!$product) {
                    throw new Exception("Product #{$productId} not found.");
                }

                $sku = $product->sku ?? ($product->product_code ?? ('PRD-' . $product->id));
                $qtyBefore = (int) ($product->stock ?? 0);
                $currentCost = (float) ($product->purchase_cost ?? ($product->purchase_price ?? 0.00));

                if (!is_null($newCost) && $applyWac && $newStock > $qtyBefore) {
                    $incomingQty = $newStock - $qtyBefore;
                    $finalCost = self::calculateWAC($qtyBefore, $currentCost, $incomingQty, $newCost);
                } elseif (!is_null($newCost)) {
                    $finalCost = $newCost;
                } else {
                    $finalCost = $currentCost;
                }

                $updateData = [
                    'stock'         => $newStock,
                    'purchase_cost' => round($finalCost, 2),
                    'updated_at'    => now(),
                ];

                if (Schema::hasColumn('products', 'purchase_price')) {
                    $updateData['purchase_price'] = round($finalCost, 2);
                }

                DB::table('products')->where('id', $productId)->update($updateData);
            }

            $diff = $newStock - $qtyBefore;

            self::logMovement([
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'sku'               => $sku,
                'movement_type'     => $movementType,
                'quantity_before'   => $qtyBefore,
                'quantity_change'   => $diff,
                'quantity_after'    => $newStock,
                'unit_cost'         => round($finalCost, 2),
                'unit_price'        => null,
                'reference_id'      => $referenceId,
                'source'            => $source,
                'idempotency_token' => $idempotencyToken,
                'created_by'        => $userId ?? auth()->id(),
                'notes'             => $notes ?? "Stock adjusted from {$qtyBefore} to {$newStock}",
            ]);

            return [
                'success'         => true,
                'product_id'      => $productId,
                'variant_id'      => $variantId,
                'sku'             => $sku,
                'quantity_before' => $qtyBefore,
                'quantity_after'  => $newStock,
                'difference'      => $diff,
                'purchase_cost'   => round($finalCost, 2),
            ];
        };

        return DB::transactionLevel() > 0 ? $execute() : DB::transaction($execute);
    }

    /**
     * WAC Calculator
     */
    public static function calculateWAC(int $currentStock, float $currentCost, int $incomingQty, float $incomingCost): float
    {
        if ($incomingQty <= 0) {
            return max(0.00, $currentCost);
        }

        if ($currentStock <= 0) {
            return max(0.00, $incomingCost);
        }

        $totalUnits = $currentStock + $incomingQty;
        $totalValuation = ($currentStock * $currentCost) + ($incomingQty * $incomingCost);

        return round($totalValuation / $totalUnits, 2);
    }

    /**
     * Ledger Aggregator
     */
    public static function getOpeningAndClosingBalance(
        ?int $productId = null,
        ?int $variantId = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        if (!Schema::hasTable('inventory_movements')) {
            return [
                'opening_qty'        => 0,
                'opening_value'      => 0.00,
                'stock_in_qty'       => 0,
                'stock_in_value'     => 0.00,
                'stock_out_qty'      => 0,
                'stock_out_value'    => 0.00,
                'sales_qty'          => 0,
                'sales_revenue'      => 0.00,
                'sales_cogs'         => 0.00,
                'gross_profit'       => 0.00,
                'closing_qty'        => 0,
                'closing_value'      => 0.00,
            ];
        }

        $start = !empty($startDate) ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = !empty($endDate) ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $baseQuery = DB::table('inventory_movements');
        if (!empty($productId)) {
            $baseQuery->where('product_id', $productId);
        }
        if (!empty($variantId)) {
            $baseQuery->where('variant_id', $variantId);
        }

        $openingQty = (int) (clone $baseQuery)
            ->where('created_at', '<', $start)
            ->sum('quantity_change');

        $openingValue = (float) (clone $baseQuery)
            ->where('created_at', '<', $start)
            ->where('quantity_change', '>', 0)
            ->sum(DB::raw('quantity_change * COALESCE(unit_cost, 0)'));

        $periodQuery = (clone $baseQuery)->whereBetween('created_at', [$start, $end]);

        $stockInQty = (int) (clone $periodQuery)
            ->where('quantity_change', '>', 0)
            ->sum('quantity_change');

        $stockInValue = (float) (clone $periodQuery)
            ->where('quantity_change', '>', 0)
            ->sum(DB::raw('quantity_change * COALESCE(unit_cost, 0)'));

        $stockOutQty = (int) abs((clone $periodQuery)
            ->where('quantity_change', '<', 0)
            ->sum('quantity_change'));

        $stockOutValue = (float) (clone $periodQuery)
            ->where('quantity_change', '<', 0)
            ->sum(DB::raw('ABS(quantity_change) * COALESCE(unit_cost, 0)'));

        $salesMovements = (clone $periodQuery)->whereIn('movement_type', ['checkout_sale', 'pos_sale', 'order_processing_deduct']);
        $salesQty = (int) abs((clone $salesMovements)->sum('quantity_change'));
        $salesRevenue = (float) (clone $salesMovements)->sum(DB::raw('ABS(quantity_change) * COALESCE(unit_price, 0)'));
        $salesCogs = (float) (clone $salesMovements)->sum(DB::raw('ABS(quantity_change) * COALESCE(unit_cost, 0)'));
        $grossProfit = max(0.00, $salesRevenue - $salesCogs);

        $closingQty = $openingQty + $stockInQty - $stockOutQty;

        $currentCost = 0.00;
        if (!empty($variantId) && Schema::hasTable('product_variants')) {
            $vRow = DB::table('product_variants')->where('id', $variantId)->first();
            if ($vRow) {
                $currentCost = (float) ($vRow->purchase_cost ?? ($vRow->purchase_price ?? 0.00));
            }
        } elseif (!empty($productId) && Schema::hasTable('products')) {
            $pRow = DB::table('products')->where('id', $productId)->first();
            if ($pRow) {
                $currentCost = (float) ($pRow->purchase_cost ?? ($pRow->purchase_price ?? 0.00));
            }
        }

        $closingValue = round($closingQty * $currentCost, 2);

        return [
            'period_start'       => $start->toDateTimeString(),
            'period_end'         => $end->toDateTimeString(),
            'opening_qty'        => $openingQty,
            'opening_value'      => round($openingValue, 2),
            'stock_in_qty'       => $stockInQty,
            'stock_in_value'     => round($stockInValue, 2),
            'stock_out_qty'      => $stockOutQty,
            'stock_out_value'    => round($stockOutValue, 2),
            'sales_qty'          => $salesQty,
            'sales_revenue'      => round($salesRevenue, 2),
            'sales_cogs'         => round($salesCogs, 2),
            'gross_profit'       => round($grossProfit, 2),
            'closing_qty'        => $closingQty,
            'closing_value'      => $closingValue > 0 ? $closingValue : round($stockInValue - $salesCogs, 2),
        ];
    }

    /**
     * Parent Product Stock Sync
     */
    public static function syncParentProductStock(int $productId): int
    {
        if (!Schema::hasTable('product_variants')) {
            return 0;
        }

        $variantRows = DB::table('product_variants')
            ->where('product_id', $productId)
            ->get();

        $totalVariantStock = (int) $variantRows->sum('stock');
        $updateData = [
            'stock'      => $totalVariantStock,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('products', 'estimated_order_qty') && Schema::hasColumn('product_variants', 'estimated_order_qty')) {
            $updateData['estimated_order_qty'] = (int) $variantRows->sum('estimated_order_qty');
        }

        if (Schema::hasColumn('products', 'reserved_stock') && Schema::hasColumn('product_variants', 'reserved_stock')) {
            $updateData['reserved_stock'] = (int) $variantRows->sum('reserved_stock');
        }

        DB::table('products')
            ->where('id', $productId)
            ->update($updateData);

        return $totalVariantStock;
    }

    /**
     * Bulk Sync Variable Products
     */
    public static function syncAllVariableProductStocks(): int
    {
        if (!Schema::hasTable('product_variants')) {
            return 0;
        }

        $productIds = DB::table('product_variants')
            ->distinct()
            ->pluck('product_id');

        $updatedCount = 0;
        foreach ($productIds as $pId) {
            self::syncParentProductStock((int) $pId);
            $updatedCount++;
        }

        return $updatedCount;
    }

    /**
     * Check Stock Availability
     */
    public static function checkStockAvailability(int $productId, ?int $variantId, int $quantity = 1): array
    {
        if (!empty($variantId)) {
            $variant = DB::table('product_variants')
                ->where('id', $variantId)
                ->where('product_id', $productId)
                ->first();

            if (!$variant) {
                return [
                    'available'         => false,
                    'stock'             => 0,
                    'estimated_qty'     => 0,
                    'available_to_sell' => 0,
                    'message'           => 'Variant does not exist.',
                ];
            }

            $currentStock = (int) ($variant->stock ?? 0);
            $estimatedQty = (int) ($variant->estimated_order_qty ?? 0);
            $reservedStock = (int) ($variant->reserved_stock ?? 0);
            $availableToSell = max(0, $currentStock - $reservedStock);

            return [
                'available'         => $currentStock >= $quantity,
                'stock'             => $currentStock,
                'estimated_qty'     => $estimatedQty,
                'reserved_stock'    => $reservedStock,
                'available_to_sell' => $availableToSell,
                'requested'         => $quantity,
                'sku'               => $variant->product_code ?? ($variant->sku ?? ''),
                'title'             => trim(($variant->color ?? '') . ' ' . ($variant->size ?? '')),
                'message'           => $currentStock >= $quantity ? 'In stock' : 'Out of stock / Insufficient quantity',
            ];
        }

        $product = DB::table('products')->where('id', $productId)->first();
        if (!$product) {
            return [
                'available'         => false,
                'stock'             => 0,
                'estimated_qty'     => 0,
                'available_to_sell' => 0,
                'message'           => 'Product not found.',
            ];
        }

        $currentStock = (int) ($product->stock ?? 0);
        $estimatedQty = (int) ($product->estimated_order_qty ?? 0);
        $reservedStock = (int) ($product->reserved_stock ?? 0);
        $availableToSell = max(0, $currentStock - $reservedStock);

        return [
            'available'         => $currentStock >= $quantity,
            'stock'             => $currentStock,
            'estimated_qty'     => $estimatedQty,
            'reserved_stock'    => $reservedStock,
            'available_to_sell' => $availableToSell,
            'requested'         => $quantity,
            'sku'               => $product->sku ?? ($product->product_code ?? ''),
            'title'             => $product->title ?? 'Product',
            'message'           => $currentStock >= $quantity ? 'In stock' : 'Out of stock',
        ];
    }

    /**
     * Inventory Valuation
     */
    public static function getInventoryValuation(): array
    {
        $hasVariantTable = Schema::hasTable('product_variants');
        $variableProductIds = $hasVariantTable
            ? DB::table('product_variants')->distinct()->pluck('product_id')->toArray()
            : [];

        $simpleProducts = DB::table('products')
            ->whereNotIn('id', $variableProductIds)
            ->select('id', 'stock', 'purchase_cost', 'purchase_price')
            ->get();

        $simpleTotalUnits = 0;
        $simpleValuation = 0.00;

        foreach ($simpleProducts as $p) {
            $stock = max(0, (int) ($p->stock ?? 0));
            $cost = (float) ($p->purchase_cost ?? ($p->purchase_price ?? 0.00));
            $simpleTotalUnits += $stock;
            $simpleValuation += ($stock * $cost);
        }

        $variantTotalUnits = 0;
        $variantValuation = 0.00;

        if ($hasVariantTable) {
            $variants = DB::table('product_variants')
                ->select('id', 'product_id', 'stock', 'purchase_cost')
                ->get();

            foreach ($variants as $v) {
                $stock = max(0, (int) ($v->stock ?? 0));
                $cost = (float) ($v->purchase_cost ?? 0.00);
                $variantTotalUnits += $stock;
                $variantValuation += ($stock * $cost);
            }
        }

        $totalUnits = $simpleTotalUnits + $variantTotalUnits;
        $totalValuation = $simpleValuation + $variantValuation;

        return [
            'total_units'       => $totalUnits,
            'total_valuation'   => round($totalValuation, 2),
            'simple_units'      => $simpleTotalUnits,
            'simple_valuation'  => round($simpleValuation, 2),
            'variant_units'     => $variantTotalUnits,
            'variant_valuation' => round($variantValuation, 2),
        ];
    }

    /**
     * Internal audit log helper (Strict Schema Column Guard)
     */
    public static function logMovement(array $data): void
    {
        if (!Schema::hasTable('inventory_movements')) {
            return;
        }

        try {
            $filtered = [];
            foreach ($data as $col => $val) {
                if (Schema::hasColumn('inventory_movements', $col)) {
                    $filtered[$col] = $val;
                }
            }

            if (!isset($filtered['quantity_change']) && Schema::hasColumn('inventory_movements', 'quantity') && isset($data['quantity_change'])) {
                $filtered['quantity'] = abs($data['quantity_change']);
            }
            if (!isset($filtered['notes']) && Schema::hasColumn('inventory_movements', 'note') && isset($data['notes'])) {
                $filtered['note'] = $data['notes'];
            }
            if (!isset($filtered['reference_id']) && Schema::hasColumn('inventory_movements', 'reference_no') && isset($data['reference_id'])) {
                $filtered['reference_no'] = $data['reference_id'];
            }

            if (Schema::hasColumn('inventory_movements', 'created_at')) {
                $filtered['created_at'] = now();
            }
            if (Schema::hasColumn('inventory_movements', 'updated_at')) {
                $filtered['updated_at'] = now();
            }

            DB::table('inventory_movements')->insert($filtered);
        } catch (\Throwable $e) {
            Log::error('Inventory movement log failure: ' . $e->getMessage(), ['data' => $data]);
        }
    }
}
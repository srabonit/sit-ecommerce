<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductStock;
use App\Models\InventoryMovement;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BranchInventoryService
{
    // =========================================================================
    // 1. STOCK RETRIEVAL & AVAILABILITY CHECKS
    // =========================================================================

    /**
     * Get or initialize a product_stocks record for a branch.
     * Supports pessimistic locking (lockForUpdate) for concurrent sale protection.
     */
    public static function getStockRecord(
        int $branchId,
        int $productId,
        ?int $variantId = null,
        bool $lock = false
    ): ProductStock {
        $query = ProductStock::where('branch_id', $branchId)
            ->where('product_id', $productId);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        $record = $query->first();

        if (!$record) {
            $sku = null;
            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                $sku = $variant?->sku ?? $variant?->product_code;
            } else {
                $product = Product::find($productId);
                $sku = $product?->sku ?? $product?->product_code;
            }

            $record = ProductStock::create([
                'branch_id'         => $branchId,
                'product_id'        => $productId,
                'variant_id'        => $variantId,
                'sku'               => $sku,
                'warehouse_id'      => null,
                'quantity'          => 0,
                'reserved_quantity' => 0,
                'alert_quantity'    => 5,
            ]);
        }

        return $record;
    }

    /**
     * Get actual commercially available stock in a branch (Quantity - Reserved).
     */
    public static function getAvailableStock(int $branchId, int $productId, ?int $variantId = null): int
    {
        $query = ProductStock::where('branch_id', $branchId)
            ->where('product_id', $productId);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        $stock = $query->first();

        if ($stock) {
            return max(0, (int) $stock->quantity - (int) ($stock->reserved_quantity ?? 0));
        }

        return 0;
    }

    /**
     * Validate whether a branch has sufficient unreserved stock for an order/cart item.
     */
    public static function hasSufficientStock(
        int $branchId,
        int $productId,
        ?int $variantId,
        int $requestedQuantity
    ): bool {
        if ($requestedQuantity <= 0) {
            return true;
        }

        return self::getAvailableStock($branchId, $productId, $variantId) >= $requestedQuantity;
    }

    // =========================================================================
    // 2. STOCK IN (PURCHASE, TRANSFER RECEIVE, MANUAL IN, RETURNS)
    // =========================================================================

    /**
     * Atomically increase stock in a specific branch and record movement audit.
     */
    public static function stockIn(
        int $branchId,
        int $productId,
        ?int $variantId,
        int $quantity,
        float $unitCost = 0.00,
        string $movementType = 'manual_in',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $userId = null
    ): ProductStock {
        if ($quantity <= 0) {
            throw new Exception("Stock in quantity must be greater than zero.");
        }

        return DB::transaction(function () use (
            $branchId,
            $productId,
            $variantId,
            $quantity,
            $unitCost,
            $movementType,
            $referenceType,
            $referenceId,
            $note,
            $userId
        ) {
            $stockRecord = self::getStockRecord($branchId, $productId, $variantId, true);
            $beforeQty = (int) $stockRecord->quantity;
            $stockRecord->quantity = $beforeQty + $quantity;
            $stockRecord->save();
            $afterQty = (int) $stockRecord->quantity;

            // Schema-safe Movement Audit Trail
            $movementData = [
                'branch_id'       => $branchId,
                'warehouse_id'    => $stockRecord->warehouse_id,
                'product_id'      => $productId,
                'variant_id'      => $variantId,
                'movement_type'   => $movementType,
                'quantity_change' => $quantity,
                'quantity_before' => $beforeQty,
                'quantity_after'  => $afterQty,
                'unit_cost'       => $unitCost > 0 ? $unitCost : ($stockRecord->product?->effective_purchase_cost ?? 0.00),
                'reference_id'    => $referenceId,
                'notes'           => $note ?? "Stock In (+{$quantity}) at branch #{$branchId}",
                'created_by'      => $userId ?? Auth::id(),
            ];

            if (Schema::hasColumn('inventory_movements', 'quantity')) {
                $movementData['quantity'] = $quantity;
            }
            if (Schema::hasColumn('inventory_movements', 'reference_type')) {
                $movementData['reference_type'] = $referenceType;
            }
            if (Schema::hasColumn('inventory_movements', 'sku')) {
                $movementData['sku'] = $stockRecord->sku;
            }

            InventoryMovement::create($movementData);

            // Sync Central Master Catalog Stock
            self::syncMasterStock($productId, $variantId);

            return $stockRecord;
        });
    }

    // =========================================================================
    // 3. STOCK OUT & POS ATOMIC DEDUCTION (Fixed: quantity_change resolved)
    // =========================================================================

    /**
     * Atomically deduct stock for sales or dispatch with strict race-condition locking.
     */
    public static function stockOut(
        int $branchId,
        int $productId,
        ?int $variantId,
        int $quantity,
        float $unitCost = 0.00,
        string $movementType = 'pos_sale',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $note = null,
        ?int $userId = null,
        bool $allowNegative = false
    ): ProductStock {
        if ($quantity <= 0) {
            throw new Exception("Stock out quantity must be greater than zero.");
        }

        return DB::transaction(function () use (
            $branchId,
            $productId,
            $variantId,
            $quantity,
            $unitCost,
            $movementType,
            $referenceType,
            $referenceId,
            $note,
            $userId,
            $allowNegative
        ) {
            $stockRecord = self::getStockRecord($branchId, $productId, $variantId, true);
            $currentAvailable = (int) $stockRecord->quantity - (int) ($stockRecord->reserved_quantity ?? 0);

            if (!$allowNegative && $currentAvailable < $quantity) {
                $productName = $stockRecord->product?->name ?? "Product #{$productId}";
                $variantInfo = $variantId && $stockRecord->variant ? " ({$stockRecord->variant->variant_title})" : '';
                throw new Exception("Insufficient stock for '{$productName}{$variantInfo}' in this branch. Available: {$currentAvailable}, Required: {$quantity}.");
            }

            $beforeQty = (int) $stockRecord->quantity;
            $stockRecord->quantity = $beforeQty - $quantity;
            $stockRecord->save();
            $afterQty = (int) $stockRecord->quantity;

            // Schema-safe Movement Audit Trail (Includes quantity_change, quantity_before, quantity_after)
            $movementData = [
                'branch_id'       => $branchId,
                'warehouse_id'    => $stockRecord->warehouse_id,
                'product_id'      => $productId,
                'variant_id'      => $variantId,
                'movement_type'   => $movementType,
                'quantity_change' => -$quantity,
                'quantity_before' => $beforeQty,
                'quantity_after'  => $afterQty,
                'unit_cost'       => $unitCost > 0 ? $unitCost : ($stockRecord->product?->effective_purchase_cost ?? 0.00),
                'reference_id'    => $referenceId,
                'notes'           => $note ?? "Stock Out (-{$quantity}) from branch #{$branchId}",
                'created_by'      => $userId ?? Auth::id(),
            ];

            if (Schema::hasColumn('inventory_movements', 'quantity')) {
                $movementData['quantity'] = -$quantity;
            }
            if (Schema::hasColumn('inventory_movements', 'reference_type')) {
                $movementData['reference_type'] = $referenceType;
            }
            if (Schema::hasColumn('inventory_movements', 'sku')) {
                $movementData['sku'] = $stockRecord->sku;
            }

            InventoryMovement::create($movementData);

            // Sync Central Master Catalog Stock
            self::syncMasterStock($productId, $variantId);

            return $stockRecord;
        });
    }

    /**
     * Dedicated POS sale checkout stock deduct handler.
     */
    public static function deductPosSaleStock(
        int $branchId,
        int $orderId,
        array $items,
        ?int $userId = null
    ): void {
        DB::transaction(function () use ($branchId, $orderId, $items, $userId) {
            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
                $quantity  = (int) $item['qty'];
                $unitCost  = (float) ($item['unit_cost'] ?? 0.00);

                self::stockOut(
                    branchId: $branchId,
                    productId: $productId,
                    variantId: $variantId,
                    quantity: $quantity,
                    unitCost: $unitCost,
                    movementType: 'pos_sale',
                    referenceType: 'order',
                    referenceId: $orderId,
                    note: "POS Sale Order #{$orderId} item deduction",
                    userId: $userId ?? Auth::id(),
                    allowNegative: false
                );
            }
        });
    }

    // =========================================================================
    // 4. RESERVED STOCK LIFECYCLE
    // =========================================================================

    public static function reserveStock(int $branchId, int $productId, ?int $variantId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        return DB::transaction(function () use ($branchId, $productId, $variantId, $quantity) {
            $stockRecord = self::getStockRecord($branchId, $productId, $variantId, true);
            $available = (int) $stockRecord->quantity - (int) ($stockRecord->reserved_quantity ?? 0);

            if ($available < $quantity) {
                return false;
            }

            $stockRecord->reserved_quantity = (int) ($stockRecord->reserved_quantity ?? 0) + $quantity;
            $stockRecord->save();

            return true;
        });
    }

    public static function releaseReservedStock(int $branchId, int $productId, ?int $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($branchId, $productId, $variantId, $quantity) {
            $stockRecord = self::getStockRecord($branchId, $productId, $variantId, true);
            $newReserved = max(0, (int) ($stockRecord->reserved_quantity ?? 0) - $quantity);
            $stockRecord->reserved_quantity = $newReserved;
            $stockRecord->save();
        });
    }

    // =========================================================================
    // 5. INVENTORY AUDIT & PHYSICAL ADJUSTMENT
    // =========================================================================

    public static function adjustStock(
        int $branchId,
        int $productId,
        ?int $variantId,
        int $newPhysicalQuantity,
        string $reason = 'Physical Audit Count',
        ?int $userId = null
    ): ProductStock {
        return DB::transaction(function () use ($branchId, $productId, $variantId, $newPhysicalQuantity, $reason, $userId) {
            $stockRecord = self::getStockRecord($branchId, $productId, $variantId, true);
            $currentQty = (int) $stockRecord->quantity;
            $diff = $newPhysicalQuantity - $currentQty;

            if ($diff === 0) {
                return $stockRecord;
            }

            $stockRecord->quantity = max(0, $newPhysicalQuantity);
            $stockRecord->save();

            $movementData = [
                'branch_id'       => $branchId,
                'warehouse_id'    => $stockRecord->warehouse_id,
                'product_id'      => $productId,
                'variant_id'      => $variantId,
                'movement_type'   => $diff > 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity_change' => $diff,
                'quantity_before' => $currentQty,
                'quantity_after'  => $stockRecord->quantity,
                'unit_cost'       => (float) ($stockRecord->product?->effective_purchase_cost ?? 0.00),
                'reference_id'    => null,
                'notes'           => "Adjustment at branch #{$branchId}: {$reason} (Diff: {$diff})",
                'created_by'      => $userId ?? Auth::id(),
            ];

            if (Schema::hasColumn('inventory_movements', 'quantity')) {
                $movementData['quantity'] = $diff;
            }
            if (Schema::hasColumn('inventory_movements', 'reference_type')) {
                $movementData['reference_type'] = 'manual_adjustment';
            }
            if (Schema::hasColumn('inventory_movements', 'sku')) {
                $movementData['sku'] = $stockRecord->sku;
            }

            InventoryMovement::create($movementData);

            self::syncMasterStock($productId, $variantId);

            return $stockRecord;
        });
    }

    // =========================================================================
    // 6. MASTER CATALOG CONSISTENCY SYNC
    // =========================================================================

    public static function syncMasterStock(int $productId, ?int $variantId = null): void
    {
        try {
            if ($variantId) {
                $totalVariantStock = (int) ProductStock::where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->sum('quantity');

                ProductVariant::where('id', $variantId)->update([
                    'stock'      => max(0, $totalVariantStock),
                    'updated_at' => now(),
                ]);
            }

            $product = Product::find($productId);
            if ($product && $product->is_variable) {
                $totalSum = (int) ProductVariant::where('product_id', $productId)->sum('stock');
                Product::where('id', $productId)->update([
                    'stock'      => max(0, $totalSum),
                    'updated_at' => now(),
                ]);
            } else {
                $totalBaseStock = (int) ProductStock::where('product_id', $productId)
                    ->whereNull('variant_id')
                    ->sum('quantity');

                Product::where('id', $productId)->update([
                    'stock'      => max(0, $totalBaseStock),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to sync master stock for Product #{$productId}: " . $e->getMessage());
        }
    }

    // =========================================================================
    // 7. BRANCH LOW STOCK REPORTING
    // =========================================================================

    public static function getBranchLowStockItems(int $branchId, int $limit = 20): Collection
    {
        return ProductStock::with(['product', 'variant'])
            ->where('branch_id', $branchId)
            ->whereRaw('(`quantity` - COALESCE(`reserved_quantity`, 0)) <= COALESCE(`alert_quantity`, 5)')
            ->orderBy('quantity', 'asc')
            ->limit($limit)
            ->get();
    }
}
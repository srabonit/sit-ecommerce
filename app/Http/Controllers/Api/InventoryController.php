<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Http\Requests\Inventory\StockTransferRequest;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Get Current Stock List (Branch/Warehouse Filtered)
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductStock::with(['product', 'branch', 'warehouse']);

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $stocks = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'data'   => $stocks,
        ], 200);
    }

    /**
     * Stock Adjustment (In / Out / Manual Override)
     */
    public function adjustStock(StockAdjustmentRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $stock = ProductStock::where('product_id', $request->product_id)
                ->where('branch_id', $request->branch_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = new ProductStock([
                    'product_id'   => $request->product_id,
                    'branch_id'    => $request->branch_id,
                    'warehouse_id' => $request->warehouse_id,
                    'quantity'     => 0,
                    'alert_quantity' => $request->alert_quantity ?? 5,
                ]);
            }

            if ($request->type === 'add') {
                $stock->quantity += $request->quantity;
            } elseif ($request->type === 'subtract') {
                if ($stock->quantity < $request->quantity) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Insufficient stock for deduction.'
                    ], 422);
                }
                $stock->quantity -= $request->quantity;
            } elseif ($request->type === 'set') {
                $stock->quantity = $request->quantity;
            }

            if ($request->filled('alert_quantity')) {
                $stock->alert_quantity = $request->alert_quantity;
            }

            $stock->save();

            return response()->json([
                'status'  => true,
                'message' => 'Stock adjusted successfully.',
                'data'    => $stock->load(['product', 'branch', 'warehouse'])
            ], 200);
        });
    }

    /**
     * Transfer Stock Between Branches / Warehouses
     */
    public function transferStock(StockTransferRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            // Create Stock Transfer Record
            $transfer = StockTransfer::create([
                'transfer_no'       => 'TRF-' . strtoupper(Str::random(8)),
                'from_branch_id'    => $request->from_branch_id,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_branch_id'      => $request->to_branch_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
                'created_by'        => $request->user()->id,
                'status'            => 'completed',
                'notes'             => $request->notes,
            ]);

            foreach ($request->items as $item) {
                // Deduct from Sender
                $sourceStock = ProductStock::where('product_id', $item['product_id'])
                    ->where('branch_id', $request->from_branch_id)
                    ->where('warehouse_id', $request->from_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$sourceStock || $sourceStock->quantity < $item['quantity']) {
                    throw new \Exception("Stock unavailable for Product ID: {$item['product_id']} at source.");
                }

                $sourceStock->quantity -= $item['quantity'];
                $sourceStock->save();

                // Add to Receiver
                $destinationStock = ProductStock::where('product_id', $item['product_id'])
                    ->where('branch_id', $request->to_branch_id)
                    ->where('warehouse_id', $request->to_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$destinationStock) {
                    $destinationStock = new ProductStock([
                        'product_id'   => $item['product_id'],
                        'branch_id'    => $request->to_branch_id,
                        'warehouse_id' => $request->to_warehouse_id,
                        'quantity'     => 0,
                    ]);
                }

                $destinationStock->quantity += $item['quantity'];
                $destinationStock->save();

                // Save Item Log
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Stock transferred successfully.',
                'data'    => $transfer->load('items.product')
            ], 201);
        });
    }

    /**
     * Low Stock Alert Report
     */
    public function lowStockAlert(Request $request): JsonResponse
    {
        $stocks = ProductStock::with(['product', 'branch', 'warehouse'])
            ->whereColumn('quantity', '<=', 'alert_quantity')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $stocks,
        ], 200);
    }
}
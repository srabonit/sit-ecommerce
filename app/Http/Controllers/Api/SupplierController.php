<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductStock;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['status' => true, 'data' => Supplier::latest()->paginate(10)]);
    }

    public function storeSupplier(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'company_name' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);
        return response()->json(['status' => true, 'message' => 'Supplier created successfully.', 'data' => $supplier], 201);
    }

    public function createPurchaseOrder(Request $request): JsonResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            $po = PurchaseOrder::create([
                'po_number'    => 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'supplier_id'  => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'total_amount' => $totalAmount,
                'status'       => 'pending',
            ]);

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'unit_cost'         => $item['unit_cost'],
                    'subtotal'          => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            return response()->json(['status' => true, 'message' => 'Purchase order created.', 'data' => $po->load('items')], 201);
        });
    }

    public function receivePurchaseOrder($poId): JsonResponse
    {
        return DB::transaction(function () use ($poId) {
            $po = PurchaseOrder::with('items')->findOrFail($poId);

            if ($po->status === 'received') {
                return response()->json(['status' => false, 'message' => 'PO already received.'], 400);
            }

            foreach ($po->items as $item) {
                $stock = ProductStock::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $po->warehouse_id],
                    ['quantity' => 0]
                );
                $stock->increment('quantity', $item->quantity);
            }

            $po->update(['status' => 'received', 'received_at' => now()]);

            return response()->json(['status' => true, 'message' => 'Stock updated from Purchase Order.']);
        });
    }
}
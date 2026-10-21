<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BarcodeController extends Controller
{
    public function index()
    {
        $products = DB::table('products')
            ->select('id', 'title', 'product_code', 'barcode', 'regular_price', 'sale_price', 'thumbnail')
            ->latest('id')
            ->get();

        $orders = collect();
        if (Schema::hasTable('orders')) {
            $orders = DB::table('orders')
                ->select('id', 'invoice_no', 'grand_total', 'created_at')
                ->latest('id')
                ->limit(50)
                ->get();
        }

        $tickets = collect();
        if (Schema::hasTable('support_tickets')) {
            $tickets = DB::table('support_tickets')
                ->select('id', 'subject', 'ticket_no', 'created_at')
                ->latest('id')
                ->limit(50)
                ->get();
        }

        return view('admin.barcode.index', compact('products', 'orders', 'tickets'));
    }

    // ১. প্রোডাক্ট লাইভ সার্চ (SKU, ID, Barcode, Title)
    public function searchProduct(Request $request)
    {
        $q = trim($request->get('q', ''));
        $hasSku = Schema::hasColumn('products', 'sku');

        $query = DB::table('products');

        if (!empty($q)) {
            $query->where(function ($sub) use ($q, $hasSku) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('product_code', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhere('id', $q);

                if ($hasSku) {
                    $sub->orWhere('sku', 'like', "%{$q}%");
                }
            });
        }

        $selectCols = ['id', 'title', 'product_code', 'barcode', 'regular_price', 'sale_price', 'thumbnail'];
        if ($hasSku) {
            $selectCols[] = 'sku';
        }

        $products = $query->select($selectCols)
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    // ২. অর্ডার লাইভ সার্চ (Invoice, ID, Name, Phone, Barcode)
    public function searchOrder(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (!Schema::hasTable('orders')) {
            return response()->json([]);
        }

        $query = DB::table('orders');

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('id', $q);

                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $sub->orWhere('invoice_no', 'like', "%{$q}%");
                }
                if (Schema::hasColumn('orders', 'barcode')) {
                    $sub->orWhere('barcode', 'like', "%{$q}%");
                }
                foreach (['shipping_name', 'customer_name', 'name'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $sub->orWhere($col, 'like', "%{$q}%");
                    }
                }
                foreach (['shipping_phone', 'customer_phone', 'phone'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $sub->orWhere($col, 'like', "%{$q}%");
                    }
                }
            });
        }

        $orders = $query->select('id', 'invoice_no', 'grand_total', 'created_at')
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json($orders);
    }

    // ৩. সাপোর্ট টিকেট লাইভ সার্চ (Ticket No, ID, Subject, Customer info)
    public function searchTicket(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (!Schema::hasTable('support_tickets')) {
            return response()->json([]);
        }

        $query = DB::table('support_tickets');

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('id', $q)
                    ->orWhere('subject', 'like', "%{$q}%");

                if (Schema::hasColumn('support_tickets', 'ticket_no')) {
                    $sub->orWhere('ticket_no', 'like', "%{$q}%");
                }
                if (Schema::hasColumn('support_tickets', 'user_name')) {
                    $sub->orWhere('user_name', 'like', "%{$q}%");
                }
                if (Schema::hasColumn('support_tickets', 'user_phone')) {
                    $sub->orWhere('user_phone', 'like', "%{$q}%");
                }
            });
        }

        $tickets = $query->select('id', 'subject', 'ticket_no', 'created_at')
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json($tickets);
    }

    public function getDetails(Request $request, $id)
    {
        $type = $request->get('type', 'product');

        // ১. অর্ডার বারকোড ফেচ
        if ($type === 'order') {
            $order = DB::table('orders')->where('id', $id)->first();
            if (!$order) return response()->json(['success' => false, 'message' => 'Order not found'], 404);

            $barcode = !empty($order->barcode) ? $order->barcode : ($order->invoice_no ?? self::generateUniqueBarcode('order'));
            if (empty($order->barcode) && Schema::hasColumn('orders', 'barcode')) {
                DB::table('orders')->where('id', $id)->update(['barcode' => $barcode]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id'       => 'order-' . $order->id,
                    'title'    => 'Invoice: ' . ($order->invoice_no ?? '#' . $order->id),
                    'subtitle' => 'Order ID #' . $order->id,
                    'barcode'  => $barcode,
                    'price'    => $order->grand_total ?? 0,
                    'is_order' => true
                ]
            ]);
        }

        // ২. সাপোর্ট টিকিট টোকেন ফেচ
        if ($type === 'ticket') {
            $ticket = DB::table('support_tickets')->where('id', $id)->first();
            if (!$ticket) return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);

            $tokenCode = !empty($ticket->ticket_no) ? $ticket->ticket_no : self::generateUniqueBarcode('token');

            return response()->json([
                'success' => true,
                'data' => [
                    'id'       => 'token-' . $ticket->id,
                    'title'    => 'Token: ' . $tokenCode,
                    'subtitle' => Str::limit($ticket->subject ?? 'Support Ticket', 25),
                    'barcode'  => $tokenCode,
                    'price'    => null,
                    'is_order' => false
                ]
            ]);
        }

        // ৩. প্রোডাক্ট বারকোড ফেচ
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) return response()->json(['success' => false, 'message' => 'Product not found'], 404);

        if (empty($product->barcode)) {
            $generatedBarcode = self::generateUniqueBarcode('product');
            DB::table('products')->where('id', $id)->update(['barcode' => $generatedBarcode]);
            $product->barcode = $generatedBarcode;
        }

        $variations = collect();
        if (Schema::hasTable('product_variants')) {
            $variations = DB::table('product_variants')->where('product_id', $id)->get();
        } elseif (Schema::hasTable('product_variations')) {
            $variations = DB::table('product_variations')->where('product_id', $id)->get();
        }

        return response()->json([
            'success'    => true,
            'product'    => $product,
            'variations' => $variations
        ]);
    }

    public static function generateUniqueBarcode($type = 'product')
    {
        do {
            if ($type === 'order') {
                $code = '88' . mt_rand(1000000000, 9999999999);
                $exists = Schema::hasTable('orders') && Schema::hasColumn('orders', 'barcode') && DB::table('orders')->where('barcode', $code)->exists();
            } elseif ($type === 'token') {
                $code = '77' . mt_rand(1000000000, 9999999999);
                $exists = Schema::hasTable('support_tickets') && Schema::hasColumn('support_tickets', 'ticket_no') && DB::table('support_tickets')->where('ticket_no', $code)->exists();
            } else {
                $code = '42' . mt_rand(1000000000, 9999999999);
                $exists = DB::table('products')->where('barcode', $code)->exists();
            }
        } while ($exists);

        return $code;
    }
}
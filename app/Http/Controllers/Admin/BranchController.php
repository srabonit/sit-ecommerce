<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    /**
     * ১. সকল ব্রাঞ্চ তালিকা (AJAX লাইভ সার্চ ও পেজিনেশন)
     */
    public function index(Request $request)
    {
        $query = DB::table('branches');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->get('per_page', 20);
        $branches = $query->latest('id')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.branch.partials.branch_rows', compact('branches'))->render();
        }

        return view('admin.branch.index', compact('branches'));
    }

    /**
     * ২. নতুন ব্রাঞ্চ তৈরির ভিউ
     */
    public function create()
    {
        return view('admin.branch.create');
    }

    /**
     * ৩. ব্রাঞ্চ সংরক্ষণ
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:100',
            'address' => 'required|string|max:500',
            'code'    => 'nullable|string|unique:branches,code|max:50',
        ]);

        $code = $request->filled('code') 
            ? strtoupper(trim($request->code)) 
            : 'BR-' . strtoupper(Str::slug(substr($request->name, 0, 3))) . '-' . mt_rand(100, 999);

        DB::table('branches')->insert([
            'name'       => trim($request->name),
            'code'       => $code,
            'type'       => $request->type ?? 'branch',
            'phone'      => trim($request->phone),
            'email'      => trim($request->email),
            'address'    => trim($request->address),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.branch.index')->with('success', 'Branch created successfully.');
    }

    /**
     * ৪. ব্রাঞ্চ এডিট ভিউ
     */
    public function edit($id)
    {
        $branch = DB::table('branches')->where('id', $id)->first();
        if (!$branch) abort(404, 'Branch not found');

        return view('admin.branch.edit', compact('branch'));
    }

    /**
     * ৫. ব্রাঞ্চ আপডেট
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:100',
            'address' => 'required|string|max:500',
            'code'    => 'required|string|max:50|unique:branches,code,' . $id,
        ]);

        DB::table('branches')->where('id', $id)->update([
            'name'       => trim($request->name),
            'code'       => strtoupper(trim($request->code)),
            'type'       => $request->type ?? 'branch',
            'phone'      => trim($request->phone),
            'email'      => trim($request->email),
            'address'    => trim($request->address),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.branch.index')->with('success', 'Branch details updated successfully.');
    }

    /**
     * ৬. অ্যাক্টিভ / ইনঅ্যাক্টিভ টগল (AJAX)
     */
    public function toggleStatus($id)
    {
        $branch = DB::table('branches')->where('id', $id)->first();
        if (!$branch) return response()->json(['success' => false, 'message' => 'Branch not found.'], 404);

        $newStatus = !$branch->is_active;
        DB::table('branches')->where('id', $id)->update([
            'is_active'  => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success'   => true,
            'is_active' => $newStatus,
            'message'   => $newStatus ? 'Branch activated.' : 'Branch deactivated.'
        ]);
    }

    /**
     * ৭. ব্রাঞ্চ ডিলিট (AJAX)
     */
    public function destroy($id)
    {
        $branch = DB::table('branches')->where('id', $id)->first();
        if (!$branch) return response()->json(['success' => false, 'message' => 'Branch not found.'], 404);

        $hasStaff = DB::table('users')->where('branch_id', $id)->exists();
        $hasStock = DB::table('product_stocks')->where('branch_id', $id)->where('quantity', '>', 0)->exists();

        if ($hasStaff || $hasStock) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete branch! It contains active staff members or product stocks.'
            ], 422);
        }

        DB::table('branches')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => "Branch '{$branch->name}' deleted successfully."
        ]);
    }

    /**
     * ৮. New Assign: ব্রাঞ্চে প্রোডাক্ট পাঠানোর ভিউ
     */
    public function newAssign()
    {
        $branches = DB::table('branches')->where('is_active', true)->get();
        $products = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.title',
                'products.product_code',
                'products.stock',
                'categories.name as category_name'
            )
            ->orderBy('products.title', 'asc')
            ->get();

        return view('admin.branch.new_assign', compact('branches', 'products'));
    }

    /**
     * ৯. New Assign: প্রোডাক্ট স্টক ব্রাঞ্চে স্থানান্তর ও সংরক্ষণ
     */
    public function storeAssign(Request $request)
    {
        $request->validate([
            'branch_id'     => 'required|exists:branches,id',
            'transfer_date' => 'required|date',
            'product_ids'   => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities'    => 'required|array',
            'quantities.*'  => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $transferNo = 'TRF-' . date('Ymd') . '-' . mt_rand(1000, 9999);

            $transferId = DB::table('stock_transfers')->insertGetId([
                'transfer_no'        => $transferNo,
                'from_branch_id'     => null,
                'from_warehouse_id'  => null,
                'to_branch_id'       => $request->branch_id,
                'to_warehouse_id'    => null,
                'created_by'         => Auth::id() ?? 1,
                'status'             => 'completed',
                'notes'              => $request->notes ?? 'Products assigned directly to branch',
                'created_at'         => $request->transfer_date . ' ' . date('H:i:s'),
                'updated_at'         => now(),
            ]);

            foreach ($request->product_ids as $index => $prodId) {
                $qty = (int) $request->quantities[$index];

                DB::table('stock_transfer_items')->insert([
                    'stock_transfer_id' => $transferId,
                    'product_id'        => $prodId,
                    'quantity'          => $qty,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $stockRecord = DB::table('product_stocks')
                    ->where('product_id', $prodId)
                    ->where('branch_id', $request->branch_id)
                    ->first();

                if ($stockRecord) {
                    DB::table('product_stocks')
                        ->where('id', $stockRecord->id)
                        ->increment('quantity', $qty);
                } else {
                    DB::table('product_stocks')->insert([
                        'product_id'     => $prodId,
                        'branch_id'      => $request->branch_id,
                        'warehouse_id'   => null,
                        'quantity'       => $qty,
                        'alert_quantity' => 5,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                DB::table('products')->where('id', $prodId)->decrement('stock', $qty);
            }

            DB::commit();
            return redirect()->route('admin.branch.assign.history')->with('success', "Products successfully assigned. Transfer No: {$transferNo}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to assign products: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * ১০. Assign History: সকল ট্রান্সফার ইতিহাস
     */
    public function assignHistory(Request $request)
    {
        $query = DB::table('stock_transfers')
            ->leftJoin('branches', 'stock_transfers.to_branch_id', '=', 'branches.id')
            ->leftJoin('users', 'stock_transfers.created_by', '=', 'users.id')
            ->select(
                'stock_transfers.*',
                'branches.name as branch_name',
                'branches.code as branch_code',
                'users.name as assigned_by'
            );

        if ($request->filled('branch_id')) {
            $query->where('stock_transfers.to_branch_id', $request->branch_id);
        }

        $transfers = $query->latest('stock_transfers.id')->paginate(20)->withQueryString();
        $branches = DB::table('branches')->get();

        if ($request->ajax()) {
            return view('admin.branch.partials.assign_history_rows', compact('transfers'))->render();
        }

        return view('admin.branch.assign_history', compact('transfers', 'branches'));
    }

    /**
     * ১১. নির্দিষ্ট ট্রান্সফারের আইটেম ডিটেইলস (AJAX Modal)
     */
    public function showAssignDetails($id)
    {
        $transfer = DB::table('stock_transfers')
            ->leftJoin('branches', 'stock_transfers.to_branch_id', '=', 'branches.id')
            ->leftJoin('users', 'stock_transfers.created_by', '=', 'users.id')
            ->where('stock_transfers.id', $id)
            ->select('stock_transfers.*', 'branches.name as branch_name', 'users.name as creator_name')
            ->first();

        if (!$transfer) return response()->json(['success' => false, 'message' => 'Not found.'], 404);

        $items = DB::table('stock_transfer_items')
            ->join('products', 'stock_transfer_items.product_id', '=', 'products.id')
            ->where('stock_transfer_items.stock_transfer_id', $id)
            ->select('products.title', 'products.product_code', 'stock_transfer_items.quantity')
            ->get();

        return response()->json([
            'success'  => true,
            'transfer' => $transfer,
            'items'    => $items
        ]);
    }

    /**
     * ১২. Branch Products: ব্রাঞ্চ অনুযায়ী প্রোডাক্ট স্টক তালিকা
     */
    public function branchProducts(Request $request)
    {
        $query = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->join('branches', 'product_stocks.branch_id', '=', 'branches.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'product_stocks.id as stock_id',
                'product_stocks.quantity as current_stock',
                'product_stocks.alert_quantity',
                'products.id as product_id',
                'products.title as product_title',
                'products.product_code',
                'products.regular_price',
                'products.sale_price',
                'categories.name as category_name',
                'branches.name as branch_name',
                'branches.id as branch_id'
            );

        if ($request->filled('branch_id')) {
            $query->where('product_stocks.branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('products.title', 'like', "%{$s}%")
                  ->orWhere('products.product_code', 'like', "%{$s}%");
            });
        }

        $stocks = $query->latest('product_stocks.id')->paginate(20)->withQueryString();
        $branches = DB::table('branches')->get();

        if ($request->ajax()) {
            return view('admin.branch.partials.branch_product_rows', compact('stocks'))->render();
        }

        return view('admin.branch.products', compact('stocks', 'branches'));
    }

    /**
     * ১৩. ব্রাঞ্চ প্রোডাক্ট স্টক ইনক্রিমেন্ট/ডিক্রিমেন্ট (AJAX)
     */
    public function updateProductStock(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:0']);

        DB::table('product_stocks')->where('id', $id)->update([
            'quantity'   => (int) $request->quantity,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Stock updated successfully.']);
    }

    /**
     * ১৪. Branch Orders: ব্রাঞ্চভিত্তিক সেলস ও অর্ডার
     */
    public function branchOrders(Request $request)
    {
        $query = DB::table('orders')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->select('orders.*', 'branches.name as branch_name');

        if ($request->filled('branch_id')) {
            $query->where('orders.branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('orders.id', 'like', "%{$s}%")
                  ->orWhere('orders.invoice_no', 'like', "%{$s}%");
            });
        }

        $orders = $query->latest('orders.id')->paginate(20)->withQueryString();
        $branches = DB::table('branches')->get();

        if ($request->ajax()) {
            return view('admin.branch.partials.branch_order_rows', compact('orders'))->render();
        }

        return view('admin.branch.orders', compact('orders', 'branches'));
    }

    /**
     * ১৫. AJAX প্রোডাক্ট লাইভ সার্চ (ID, Title, SKU/Code, Category Name)
     */
    public function searchProductsAjax(Request $request)
    {
        $q = trim($request->get('q', ''));

        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.title',
                'products.product_code',
                'products.stock',
                'products.regular_price',
                'categories.name as category_name'
            );

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                if (is_numeric($q)) {
                    $sub->where('products.id', $q);
                }
                $sub->orWhere('products.title', 'like', "%{$q}%")
                    ->orWhere('products.product_code', 'like', "%{$q}%")
                    ->orWhere('categories.name', 'like', "%{$q}%");
            });
        }

        $products = $query->latest('products.id')->take(30)->get();

        return response()->json($products);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\InventoryMovement;
use App\Services\ActivityLogger;
use App\Services\BranchAccessService;
use App\Services\BranchInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    /**
     * Schema-safe resolver for MySQL ENUM or strict 'type' column in branches table
     */
    protected function resolveBranchTypeColumnValue(?string $candidate = null): ?string
    {
        if (!Schema::hasColumn('branches', 'type')) {
            return null;
        }

        try {
            $tableName = DB::getTablePrefix() . 'branches';
            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}` WHERE Field = 'type'");
            if (!empty($columns)) {
                $col = array_change_key_case((array)$columns[0], CASE_LOWER);
                $typeDef = strtolower(trim((string)($col['type'] ?? '')));
                $default = $col['default'] ?? null;

                if (str_starts_with($typeDef, 'enum(')) {
                    preg_match_all("/'([^']+)'/", $typeDef, $matches);
                    $allowed = $matches[1] ?? [];

                    if (!empty($allowed)) {
                        $check = strtolower(trim((string)$candidate));
                        if ($check !== '' && in_array($check, $allowed, true)) {
                            return $check;
                        }

                        if (!empty($default) && in_array($default, $allowed, true)) {
                            return $default;
                        }

                        foreach (['branch', 'sub', 'outlet', 'retail', 'sub_branch'] as $fallback) {
                            if (in_array($fallback, $allowed, true)) {
                                return $fallback;
                            }
                        }

                        return $allowed[0];
                    }
                }

                if (str_contains($typeDef, 'int')) {
                    return is_numeric($candidate) ? (string)(int)$candidate : '0';
                }

                if (preg_match('/varchar\((\d+)\)/', $typeDef, $lenMatch)) {
                    $maxLen = (int)$lenMatch[1];
                    $val = $candidate ?: 'branch';
                    return substr($val, 0, $maxLen);
                }
            }
        } catch (\Throwable $e) {
            // safely fallback
        }

        return $candidate ?: 'branch';
    }

    /**
     * Schema-safe resolver for MySQL ENUM / INT 'status' column in stock_transfers table
     */
    protected function resolveTransferStatusColumnValue(?string $candidate = 'received'): mixed
    {
        try {
            $tableName = DB::getTablePrefix() . 'stock_transfers';
            $columns = DB::select("SHOW COLUMNS FROM `{$tableName}` WHERE Field = 'status'");
            
            if (!empty($columns)) {
                $col = array_change_key_case((array)$columns[0], CASE_LOWER);
                $typeDef = strtolower(trim((string)($col['type'] ?? '')));
                $default = $col['default'] ?? null;

                if (str_contains($typeDef, 'int')) {
                    if (is_numeric($candidate)) {
                        return (int)$candidate;
                    }
                    if (!is_null($default) && is_numeric($default)) {
                        return (int)$default;
                    }
                    return 1;
                }

                if (str_starts_with($typeDef, 'enum(')) {
                    preg_match_all("/'([^']+)'/", $typeDef, $matches);
                    $allowed = $matches[1] ?? [];

                    if (!empty($allowed)) {
                        $candLower = strtolower(trim((string)$candidate));
                        foreach ($allowed as $val) {
                            if (strtolower($val) === $candLower) {
                                return $val;
                            }
                        }

                        $priorityStatuses = ['completed', 'received', 'approved', 'transferred', 'delivered', 'success', 'pending'];
                        foreach ($priorityStatuses as $pStatus) {
                            foreach ($allowed as $val) {
                                if (strtolower($val) === $pStatus) {
                                    return $val;
                                }
                            }
                        }

                        if (!empty($default) && in_array($default, $allowed, true)) {
                            return $default;
                        }

                        return $allowed[0];
                    }
                }

                if (preg_match('/(?:varchar|char)\((\d+)\)/', $typeDef, $lenMatch)) {
                    $maxLen = (int) $lenMatch[1];
                    $val = (string) $candidate;
                    return substr($val, 0, $maxLen);
                }
            }
        } catch (\Throwable $e) {
            // safely fallback
        }

        return $candidate ?: 'received';
    }

    /**
     * 100% Schema-Safe Inventory Movement Logger (Auto-detects qty vs quantity)
     */
    protected function recordInventoryMovement(array $params): void
    {
        if (!Schema::hasTable('inventory_movements')) {
            return;
        }

        try {
            $columns = Schema::getColumnListing('inventory_movements');
            $insert = [];

            if (in_array('branch_id', $columns) && array_key_exists('branch_id', $params)) {
                $insert['branch_id'] = $params['branch_id'];
            }
            if (in_array('warehouse_id', $columns) && array_key_exists('warehouse_id', $params)) {
                $insert['warehouse_id'] = $params['warehouse_id'];
            }
            if (in_array('product_id', $columns) && array_key_exists('product_id', $params)) {
                $insert['product_id'] = $params['product_id'];
            }
            if (in_array('variant_id', $columns) && array_key_exists('variant_id', $params)) {
                $insert['variant_id'] = $params['variant_id'];
            }

            // Movement type / Type mapping
            $type = $params['movement_type'] ?? ($params['type'] ?? 'transfer_in');
            if (in_array('movement_type', $columns)) {
                $insert['movement_type'] = $type;
            } elseif (in_array('type', $columns)) {
                $insert['type'] = $type;
            }

            // Quantity mapping (checks 'qty', 'quantity', 'stock_in', etc.)
            $qty = $params['quantity'] ?? ($params['qty'] ?? 0);
            if (in_array('qty', $columns)) {
                $insert['qty'] = $qty;
            } elseif (in_array('quantity', $columns)) {
                $insert['quantity'] = $qty;
            } elseif (in_array('amount', $columns)) {
                $insert['amount'] = $qty;
            } elseif (in_array('stock_in', $columns) && $qty >= 0) {
                $insert['stock_in'] = $qty;
            } elseif (in_array('in_qty', $columns) && $qty >= 0) {
                $insert['in_qty'] = $qty;
            }

            // Cost mapping
            $cost = $params['unit_cost'] ?? ($params['cost'] ?? 0.00);
            if (in_array('unit_cost', $columns)) {
                $insert['unit_cost'] = $cost;
            } elseif (in_array('cost', $columns)) {
                $insert['cost'] = $cost;
            }

            // Reference mapping
            if (in_array('reference_type', $columns) && array_key_exists('reference_type', $params)) {
                $insert['reference_type'] = $params['reference_type'];
            }
            if (in_array('reference_id', $columns) && array_key_exists('reference_id', $params)) {
                $insert['reference_id'] = $params['reference_id'];
            }

            // Notes / Remarks
            $note = $params['note'] ?? ($params['remarks'] ?? '');
            if (in_array('note', $columns)) {
                $insert['note'] = $note;
            } elseif (in_array('remarks', $columns)) {
                $insert['remarks'] = $note;
            }

            // User / Creator
            $userId = $params['created_by'] ?? ($params['user_id'] ?? (Auth::id() ?? 1));
            if (in_array('created_by', $columns)) {
                $insert['created_by'] = $userId;
            } elseif (in_array('user_id', $columns)) {
                $insert['user_id'] = $userId;
            }

            if (in_array('created_at', $columns)) {
                $insert['created_at'] = now();
            }
            if (in_array('updated_at', $columns)) {
                $insert['updated_at'] = now();
            }

            if (!empty($insert)) {
                DB::table('inventory_movements')->insert($insert);
            }
        } catch (\Throwable $e) {
            Log::warning('Inventory movement logging skipped: ' . $e->getMessage());
        }
    }

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
        $managers = User::where('is_active', true)
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                  ->orWhereIn('role', ['admin', 'manager', 'branch_manager', 'staff']);
            })
            ->orderBy('name', 'asc')
            ->get();

        $warehouses = Schema::hasTable('warehouses') 
            ? DB::table('warehouses')->where('is_active', true)->get() 
            : collect();

        return view('admin.branch.create', compact('managers', 'warehouses'));
    }

    /**
     * ৩. ব্রাঞ্চ সংরক্ষণ
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:150',
            'phone'                 => 'required|string|max:20',
            'email'                 => 'nullable|email|max:100',
            'address'               => 'required|string|max:500',
            'code'                  => 'nullable|string|unique:branches,code|max:50',
            'branch_type'           => 'nullable|string|max:50',
            'manager_id'            => 'nullable|exists:users,id',
            'assigned_warehouse_id' => 'nullable|integer',
            'opening_date'          => 'nullable|date',
            'opening_hours'         => 'nullable|string|max:100',
            'catalog_access_mode'   => 'nullable|string|in:all_catalog,assigned_only',
        ]);

        $code = $request->filled('code') 
            ? strtoupper(trim($request->code)) 
            : 'BR-' . strtoupper(Str::slug(substr($request->name, 0, 3))) . '-' . mt_rand(100, 999);

        $branchType = $request->branch_type ?? ($request->type ?? 'retail');
        $resolvedType = $this->resolveBranchTypeColumnValue($request->type ?? $request->branch_type);

        $catalogAccessMode = strtolower(trim((string)$request->input('catalog_access_mode', 'all_catalog')));
        if (!in_array($catalogAccessMode, ['all_catalog', 'assigned_only'], true)) {
            $catalogAccessMode = 'all_catalog';
        }

        $data = [
            'name'       => trim($request->name),
            'code'       => $code,
            'phone'      => trim($request->phone),
            'email'      => trim($request->email),
            'address'    => trim($request->address),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('branches', 'type') && $resolvedType !== null) {
            $data['type'] = $resolvedType;
        }
        if (Schema::hasColumn('branches', 'branch_code')) {
            $data['branch_code'] = $code;
        }
        if (Schema::hasColumn('branches', 'branch_type')) {
            $data['branch_type'] = $branchType;
        }
        if (Schema::hasColumn('branches', 'manager_id')) {
            $data['manager_id'] = $request->manager_id;
        }
        if (Schema::hasColumn('branches', 'status')) {
            $data['status'] = 'active';
        }
        if (Schema::hasColumn('branches', 'assigned_warehouse_id')) {
            $data['assigned_warehouse_id'] = $request->assigned_warehouse_id;
        }
        if (Schema::hasColumn('branches', 'opening_date')) {
            $data['opening_date'] = $request->opening_date;
        }
        if (Schema::hasColumn('branches', 'opening_hours')) {
            $data['opening_hours'] = $request->opening_hours;
        }
        if (Schema::hasColumn('branches', 'cash_account_id')) {
            $data['cash_account_id'] = $request->cash_account_id;
        }
        if (Schema::hasColumn('branches', 'bank_account_id')) {
            $data['bank_account_id'] = $request->bank_account_id;
        }
        if (Schema::hasColumn('branches', 'catalog_access_mode')) {
            $data['catalog_access_mode'] = $catalogAccessMode;
        }
        if (Schema::hasColumn('branches', 'settings')) {
            $data['settings'] = json_encode([
                'pos_enabled'               => (bool) $request->input('pos_enabled', true),
                'allow_negative_stock_sale' => (bool) $request->input('allow_negative_stock_sale', false),
                'tax_percentage'            => (float) $request->input('tax_percentage', 0.00),
                'receipt_header'            => $request->input('receipt_header', ''),
                'receipt_footer'            => $request->input('receipt_footer', 'Thank you for shopping with us!'),
                'catalog_access_mode'       => $catalogAccessMode,
            ]);
        }

        $branchId = DB::table('branches')->insertGetId($data);

        if ($request->filled('manager_id') && Schema::hasTable('branch_user')) {
            DB::table('branch_user')->updateOrInsert(
                ['branch_id' => $branchId, 'user_id' => $request->manager_id],
                [
                    'is_primary'            => true,
                    'role_in_branch'        => 'branch_manager',
                    'can_access_pos'        => true,
                    'can_manage_inventory'  => true,
                    'can_view_reports'      => true,
                    'is_active'             => true,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]
            );
        }

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('branch_created', 'branch', "Branch {$request->name} ({$code}) created", [
                'record_id'  => $branchId,
                'record_ref' => $code,
                'new_values' => $data,
            ]);
        }

        return redirect()->route('admin.branch.index')->with('success', 'Branch created successfully.');
    }

    /**
     * ৪. ব্রাঞ্চ এডিট ভিউ
     */
    public function edit($id)
    {
        $branch = DB::table('branches')->where('id', $id)->first();
        if (!$branch) abort(404, 'Branch not found');

        $managers = User::where('is_active', true)
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                  ->orWhereIn('role', ['admin', 'manager', 'branch_manager', 'staff']);
            })
            ->orderBy('name', 'asc')
            ->get();

        $warehouses = Schema::hasTable('warehouses') 
            ? DB::table('warehouses')->where('is_active', true)->get() 
            : collect();

        return view('admin.branch.edit', compact('branch', 'managers', 'warehouses'));
    }

    /**
     * ৫. ব্রাঞ্চ আপডেট
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'                  => 'required|string|max:150',
            'phone'                 => 'required|string|max:20',
            'email'                 => 'nullable|email|max:100',
            'address'               => 'required|string|max:500',
            'code'                  => 'required|string|max:50|unique:branches,code,' . $id,
            'branch_type'           => 'nullable|string|max:50',
            'manager_id'            => 'nullable|exists:users,id',
            'assigned_warehouse_id' => 'nullable|integer',
            'opening_date'          => 'nullable|date',
            'opening_hours'         => 'nullable|string|max:100',
            'catalog_access_mode'   => 'nullable|string|in:all_catalog,assigned_only',
        ]);

        $code = strtoupper(trim($request->code));
        $branchType = $request->branch_type ?? ($request->type ?? 'retail');
        $resolvedType = $this->resolveBranchTypeColumnValue($request->type ?? $request->branch_type);

        $data = [
            'name'       => trim($request->name),
            'code'       => $code,
            'phone'      => trim($request->phone),
            'email'      => trim($request->email),
            'address'    => trim($request->address),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('branches', 'type') && $resolvedType !== null) {
            $data['type'] = $resolvedType;
        }
        if (Schema::hasColumn('branches', 'branch_code')) {
            $data['branch_code'] = $code;
        }
        if (Schema::hasColumn('branches', 'branch_type')) {
            $data['branch_type'] = $branchType;
        }
        if (Schema::hasColumn('branches', 'manager_id')) {
            $data['manager_id'] = $request->manager_id;
        }
        if (Schema::hasColumn('branches', 'assigned_warehouse_id')) {
            $data['assigned_warehouse_id'] = $request->assigned_warehouse_id;
        }
        if (Schema::hasColumn('branches', 'opening_date')) {
            $data['opening_date'] = $request->opening_date;
        }
        if (Schema::hasColumn('branches', 'opening_hours')) {
            $data['opening_hours'] = $request->opening_hours;
        }
        if (Schema::hasColumn('branches', 'cash_account_id')) {
            $data['cash_account_id'] = $request->cash_account_id;
        }
        if (Schema::hasColumn('branches', 'bank_account_id')) {
            $data['bank_account_id'] = $request->bank_account_id;
        }

        $catalogAccessMode = null;
        if ($request->filled('catalog_access_mode')) {
            $catalogAccessMode = strtolower(trim((string)$request->input('catalog_access_mode')));
            if (in_array($catalogAccessMode, ['all_catalog', 'assigned_only'], true)) {
                if (Schema::hasColumn('branches', 'catalog_access_mode')) {
                    $data['catalog_access_mode'] = $catalogAccessMode;
                }
            }
        }

        if (Schema::hasColumn('branches', 'settings')) {
            $existingSettings = [];
            $branchRow = DB::table('branches')->where('id', $id)->first();
            if ($branchRow && !empty($branchRow->settings)) {
                $existingSettings = is_array($branchRow->settings) ? $branchRow->settings : json_decode((string)$branchRow->settings, true);
                if (!is_array($existingSettings)) {
                    $existingSettings = [];
                }
            }

            if ($request->has('settings_present')) {
                $newSettings = [
                    'pos_enabled'               => (bool) $request->input('pos_enabled', true),
                    'allow_negative_stock_sale' => (bool) $request->input('allow_negative_stock_sale', false),
                    'tax_percentage'            => (float) $request->input('tax_percentage', 0.00),
                    'receipt_header'            => $request->input('receipt_header', ''),
                    'receipt_footer'            => $request->input('receipt_footer', 'Thank you for shopping with us!'),
                ];
                if ($catalogAccessMode) {
                    $newSettings['catalog_access_mode'] = $catalogAccessMode;
                } elseif (isset($existingSettings['catalog_access_mode'])) {
                    $newSettings['catalog_access_mode'] = $existingSettings['catalog_access_mode'];
                }
                $data['settings'] = json_encode(array_merge($existingSettings, $newSettings));
            } elseif ($catalogAccessMode) {
                $existingSettings['catalog_access_mode'] = $catalogAccessMode;
                $data['settings'] = json_encode($existingSettings);
            }
        }

        DB::table('branches')->where('id', $id)->update($data);

        if ($request->filled('manager_id') && Schema::hasTable('branch_user')) {
            DB::table('branch_user')->updateOrInsert(
                ['branch_id' => $id, 'user_id' => $request->manager_id],
                [
                    'is_primary'            => true,
                    'role_in_branch'        => 'branch_manager',
                    'can_access_pos'        => true,
                    'can_manage_inventory'  => true,
                    'can_view_reports'      => true,
                    'is_active'             => true,
                    'updated_at'            => now(),
                ]
            );
        }

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
        $update = [
            'is_active'  => $newStatus,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('branches', 'status')) {
            $update['status'] = $newStatus ? 'active' : 'inactive';
        }

        DB::table('branches')->where('id', $id)->update($update);

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

        $hasStaffDirect = DB::table('users')->where('branch_id', $id)->exists();
        $hasStaffPivot  = Schema::hasTable('branch_user') && DB::table('branch_user')->where('branch_id', $id)->exists();
        $hasStock       = DB::table('product_stocks')->where('branch_id', $id)->where('quantity', '>', 0)->exists();
        $hasOrders      = Schema::hasTable('orders') && DB::table('orders')->where('branch_id', $id)->exists();

        if ($hasStaffDirect || $hasStaffPivot || $hasStock || $hasOrders) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete branch! It contains active staff assignments, sales orders, or physical inventory stocks.'
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
            $transferStatus = $this->resolveTransferStatusColumnValue('received');

            $transferData = [
                'transfer_no'        => $transferNo,
                'from_branch_id'     => null,
                'from_warehouse_id'  => null,
                'to_branch_id'       => $request->branch_id,
                'to_warehouse_id'    => null,
                'created_by'         => Auth::id() ?? 1,
                'status'             => $transferStatus,
                'notes'              => $request->notes ?? 'Products assigned directly to branch',
                'created_at'         => $request->transfer_date . ' ' . date('H:i:s'),
                'updated_at'         => now(),
            ];

            if (Schema::hasColumn('stock_transfers', 'received_at')) {
                $transferData['received_at'] = now();
            }

            $transferId = DB::table('stock_transfers')->insertGetId($transferData);

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

                // Decrement central stock
                DB::table('products')->where('id', $prodId)->decrement('stock', $qty);

                // Movement Ledger Entry for branch audit (100% Schema-safe)
                $prod = DB::table('products')->where('id', $prodId)->first();
                $cost = $prod ? (float)($prod->purchase_cost ?? ($prod->purchase_price ?? 0.00)) : 0.00;

                $this->recordInventoryMovement([
                    'branch_id'      => $request->branch_id,
                    'warehouse_id'   => null,
                    'product_id'     => $prodId,
                    'variant_id'     => null,
                    'movement_type'  => 'transfer_in',
                    'quantity'       => $qty,
                    'unit_cost'      => $cost,
                    'reference_type' => 'stock_transfer',
                    'reference_id'   => $transferId,
                    'note'           => "Assigned via {$transferNo}",
                    'created_by'     => Auth::id() ?? 1,
                ]);

                // Master stock sync
                if (class_exists(BranchInventoryService::class)) {
                    BranchInventoryService::syncMasterStock($prodId);
                }
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

        $stock = ProductStock::find($id);
        if (!$stock) {
            return response()->json(['success' => false, 'message' => 'Stock record not found.'], 404);
        }

        $oldQty = (int) $stock->quantity;
        $newQty = (int) $request->quantity;
        $diff = $newQty - $oldQty;

        $stock->quantity = $newQty;
        $stock->save();

        if ($diff !== 0) {
            $this->recordInventoryMovement([
                'branch_id'      => $stock->branch_id,
                'warehouse_id'   => $stock->warehouse_id,
                'product_id'     => $stock->product_id,
                'variant_id'     => $stock->variant_id,
                'movement_type'  => $diff > 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity'       => $diff,
                'unit_cost'      => (float) ($stock->product?->effective_purchase_cost ?? 0.00),
                'reference_type' => 'manual_quick_adjustment',
                'reference_id'   => $stock->id,
                'note'           => "Quick Stock Adjustment from Branch Products table (Diff: {$diff})",
                'created_by'     => Auth::id() ?? 1,
            ]);
        }

        if (class_exists(BranchInventoryService::class)) {
            BranchInventoryService::syncMasterStock($stock->product_id, $stock->variant_id);
        }

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

    /**
     * ১৬. ব্রাঞ্চের স্টাফ ও পারমিশন ম্যানেজমেন্ট ভিউ
     */
    public function manageStaff($id)
    {
        $branch = Branch::with(['users', 'manager'])->findOrFail($id);

        $availableUsers = User::where('is_active', true)
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                  ->orWhereIn('role', ['admin', 'manager', 'branch_manager', 'cashier', 'staff']);
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.branch.manage_staff', compact('branch', 'availableUsers'));
    }

    /**
     * ১৭. ব্রাঞ্চে নতুন স্টাফ অ্যাসাইন / পারমিশন আপডেট
     */
    public function assignStaff(Request $request, $id)
    {
        $request->validate([
            'user_id'              => 'required|exists:users,id',
            'role_in_branch'       => 'required|string|max:40',
            'can_access_pos'       => 'nullable|boolean',
            'can_manage_inventory' => 'nullable|boolean',
            'can_view_reports'     => 'nullable|boolean',
            'is_primary'           => 'nullable|boolean',
        ]);

        $branch = Branch::findOrFail($id);

        $branch->assignedUsers()->syncWithoutDetaching([
            $request->user_id => [
                'role_in_branch'       => $request->role_in_branch,
                'can_access_pos'       => (bool) $request->input('can_access_pos', true),
                'can_manage_inventory' => (bool) $request->input('can_manage_inventory', false),
                'can_view_reports'     => (bool) $request->input('can_view_reports', false),
                'is_primary'           => (bool) $request->input('is_primary', false),
                'is_active'            => true,
                'updated_at'           => now(),
            ]
        ]);

        if ($request->input('is_primary')) {
            User::where('id', $request->user_id)->update(['primary_branch_id' => $branch->id]);
        }

        return redirect()->back()->with('success', 'Staff permissions updated for this branch.');
    }

    /**
     * ১৮. ব্রাঞ্চ থেকে স্টাফ অপসারণ
     */
    public function removeStaff($branchId, $userId)
    {
        $branch = Branch::findOrFail($branchId);
        $branch->assignedUsers()->detach($userId);

        return response()->json(['success' => true, 'message' => 'Staff detached from branch successfully.']);
    }

    /**
     * ১৯. ব্রাঞ্চ অপারেশনাল সেটিংস আপডেট
     */
    public function updateSettings(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $catalogMode = $request->input('catalog_access_mode', $branch->getCatalogAccessMode());
        if (!in_array($catalogMode, ['all_catalog', 'assigned_only'], true)) {
            $catalogMode = 'all_catalog';
        }

        $settings = array_merge((array) $branch->settings, [
            'pos_enabled'               => (bool) $request->input('pos_enabled', true),
            'allow_negative_stock_sale' => (bool) $request->input('allow_negative_stock_sale', false),
            'tax_percentage'            => (float) $request->input('tax_percentage', 0.00),
            'receipt_header'            => $request->input('receipt_header', ''),
            'receipt_footer'            => $request->input('receipt_footer', 'Thank you for shopping with us!'),
            'catalog_access_mode'       => $catalogMode,
        ]);

        $branch->settings = $settings;
        if (Schema::hasColumn('branches', 'catalog_access_mode')) {
            $branch->catalog_access_mode = $catalogMode;
        }
        $branch->save();

        return redirect()->back()->with('success', 'Branch operational settings updated successfully.');
    }

    /**
     * ২০. সক্রিয় ব্রাঞ্চ সেশন কনটেক্সট পরিবর্তন (Switch Branch Context)
     */
    public function switchContext(Request $request, $id)
    {
        $success = BranchAccessService::setCurrentBranchId((int) $id, Auth::user());

        if (!$success) {
            return redirect()->back()->with('error', 'You do not have authorization to switch to this branch.');
        }

        return redirect()->back()->with('success', 'Active branch switched successfully.');
    }
}
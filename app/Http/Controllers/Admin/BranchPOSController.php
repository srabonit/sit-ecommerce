<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Services\BranchAccessService;
use App\Services\BranchPOSService;
use App\Services\BranchProductService;
use App\Services\BranchReportService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchPOSController extends Controller
{
    /**
     * ১. পিওএস টার্মিনাল মূল ভিউ
     */
    public function terminal(Request $request, $branch_id = null)
    {
        $user = Auth::user();

        // 1. Resolve Target Branch ID
        $targetBranchId = (int) ($branch_id 
            ?: ($request->get('branch_id') 
            ?: BranchAccessService::getCurrentBranchId($user)));

        if (!$targetBranchId) {
            abort(403, 'No authorized branch assigned to this account.');
        }

        // 2. Validate strict POS terminal access for this branch
        $branch = BranchPOSService::validateTerminalAccess($targetBranchId, $user);

        // 3. Selectable branches for branch switching
        $selectableBranches = BranchAccessService::getSelectableBranches($user);

        // 4. Assigned Sales Representatives / Staff
        $salesPersonsQuery = User::query();
        if (Schema::hasColumn('users', 'is_active')) {
            $salesPersonsQuery->where('is_active', true);
        } elseif (Schema::hasColumn('users', 'status')) {
            $salesPersonsQuery->where('status', 'active');
        }

        $salesPersons = $salesPersonsQuery
            ->where(function ($q) use ($targetBranchId) {
                if (Schema::hasColumn('users', 'is_super_admin')) {
                    $q->where('is_super_admin', true);
                }
                if (Schema::hasColumn('users', 'primary_branch_id')) {
                    $q->orWhere('primary_branch_id', $targetBranchId);
                }
                if (Schema::hasColumn('users', 'branch_id')) {
                    $q->orWhere('branch_id', $targetBranchId);
                }
                $q->orWhereHas('assignedBranches', function ($bq) use ($targetBranchId) {
                    $bq->where('branches.id', $targetBranchId);
                    if (Schema::hasColumn('branch_user', 'is_active')) {
                        $bq->where('branch_user.is_active', true);
                    }
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        // 5. Product Categories for Quick POS Tabs
        $categoriesQuery = DB::table('categories');
        if (Schema::hasColumn('categories', 'is_active')) {
            $categoriesQuery->where('is_active', true);
        } elseif (Schema::hasColumn('categories', 'status')) {
            $categoriesQuery->where('status', 'active');
        }
        $categories = Schema::hasTable('categories')
            ? $categoriesQuery->orderBy('name', 'asc')->get()
            : collect();

        // 6. Shift Sales Metrics for POS Header
        $todayStats = BranchPOSService::getTodayPosStats($targetBranchId);

        // 7. Check if Daily Cash Register is currently open
        $registerStatus = BranchReportService::calculateLiveSessionCash($targetBranchId);

        return view('admin.pos.terminal', compact(
            'branch',
            'selectableBranches',
            'salesPersons',
            'categories',
            'todayStats',
            'registerStatus'
        ));
    }

    /**
     * ২. পিওএস লাইভ প্রোডাক্ট সার্চ ও ফিল্টার (Returns HTML Grid) - High Speed Zero-N+1 Optimized
     */
    public function searchProducts(Request $request)
    {
        $branchId = (int) ($request->get('branch_id') ?: BranchAccessService::getCurrentBranchId());
        
        $keyword = trim((string)($request->input('search', $request->input('q', ''))));
        $limit   = (int) $request->get('limit', 24);

        if (!$branchId || !BranchAccessService::canAccessBranch(Auth::user(), $branchId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized branch access.'], 403);
        }

        $query = BranchProductService::getBranchProductsQuery($branchId, true);

        // 1. Filter by Category
        if ($request->filled('category_id') && (int)$request->category_id > 0) {
            $catId = (int)$request->category_id;
            $query->where(function($q) use ($catId) {
                $q->where('products.category_id', $catId);
                if (Schema::hasTable('product_category')) {
                    $q->orWhereExists(function($sub) use ($catId) {
                        $sub->select(DB::raw(1))
                            ->from('product_category')
                            ->whereColumn('product_category.product_id', 'products.id')
                            ->where('product_category.category_id', $catId);
                    });
                }
            });
        }

        // 2. Filter by Brand
        if ($request->filled('brand_id') && (int)$request->brand_id > 0) {
            $bId = (int)$request->brand_id;
            $query->where(function($q) use ($bId) {
                $q->where('products.brand_id', $bId);
                if (Schema::hasTable('product_brand')) {
                    $q->orWhereExists(function($sub) use ($bId) {
                        $sub->select(DB::raw(1))
                            ->from('product_brand')
                            ->whereColumn('product_brand.product_id', 'products.id')
                            ->where('product_brand.brand_id', $bId);
                    });
                }
            });
        }

        // 3. Multi-word Intelligent Search Filter
        if (!empty($keyword)) {
            $term = $keyword;
            $cleanId = ltrim($term, '#');
            $words = array_filter(explode(' ', $term));

            $query->where(function ($q) use ($term, $cleanId, $words) {
                if (is_numeric($cleanId)) {
                    $q->where('products.id', (int)$cleanId);
                }
                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', 'LIKE', "%{$term}%");
                }
                if (Schema::hasColumn('products', 'product_code')) {
                    $q->orWhere('products.product_code', 'LIKE', "%{$term}%");
                }
                if (Schema::hasColumn('products', 'barcode')) {
                    $q->orWhere('products.barcode', 'LIKE', "%{$term}%");
                }

                if (Schema::hasColumn('products', 'title')) {
                    $q->orWhere('products.title', 'LIKE', "%{$term}%");
                }
                if (Schema::hasColumn('products', 'name')) {
                    $q->orWhere('products.name', 'LIKE', "%{$term}%");
                }

                if (count($words) > 1) {
                    $q->orWhere(function ($wq) use ($words) {
                        foreach ($words as $w) {
                            $w = trim($w);
                            if (strlen($w) >= 2) {
                                $wq->where(function ($subW) use ($w) {
                                    if (Schema::hasColumn('products', 'title')) {
                                        $subW->where('products.title', 'LIKE', "%{$w}%");
                                    }
                                    if (Schema::hasColumn('products', 'name')) {
                                        $subW->orWhere('products.name', 'LIKE', "%{$w}%");
                                    }
                                });
                            }
                        }
                    });
                }

                $q->orWhereHas('variants', function ($vq) use ($term) {
                    $vq->where(function ($subVq) use ($term) {
                        if (Schema::hasColumn('product_variants', 'sku')) {
                            $subVq->where('sku', 'LIKE', "%{$term}%");
                        }
                        if (Schema::hasColumn('product_variants', 'product_code')) {
                            $subVq->orWhere('product_code', 'LIKE', "%{$term}%");
                        }
                        if (Schema::hasColumn('product_variants', 'barcode')) {
                            $subVq->orWhere('barcode', 'LIKE', "%{$term}%");
                        }
                        if (Schema::hasColumn('product_variants', 'color')) {
                            $subVq->orWhere('color', 'LIKE', "%{$term}%");
                        }
                        if (Schema::hasColumn('product_variants', 'size')) {
                            $subVq->orWhere('size', 'LIKE', "%{$term}%");
                        }
                    });
                });
            });
        }

        $orderColumn = Schema::hasColumn('products', 'title') 
            ? 'products.title' 
            : (Schema::hasColumn('products', 'name') ? 'products.name' : 'products.id');

        $products = $query->orderBy($orderColumn, 'asc')->paginate($limit)->withQueryString();

        $branch = Branch::find($branchId);
        $isMainBranch = $branch 
            ? (method_exists($branch, 'isMainBranch') ? $branch->isMainBranch() : (strtolower((string)($branch->type ?? '')) === 'main'))
            : false;

        $pageProductIds = $products->pluck('id')->filter()->all();
        $pageVariantIds = $products->pluck('variants')->flatten()->pluck('id')->filter()->all();

        $productsWithAnyStock = (!empty($pageProductIds) && Schema::hasTable('product_stocks'))
            ? DB::table('product_stocks')->whereIn('product_id', $pageProductIds)->pluck('product_id')->flip()->all()
            : [];

        $variantsWithAnyStock = (!empty($pageVariantIds) && Schema::hasTable('product_stocks'))
            ? DB::table('product_stocks')->whereIn('variant_id', $pageVariantIds)->pluck('variant_id')->flip()->all()
            : [];

        $products->getCollection()->transform(function ($product) use ($branchId, $isMainBranch, $productsWithAnyStock, $variantsWithAnyStock) {
            $pricing = BranchProductService::resolvePrice($product, $branchId);
            $product->regular_price = $pricing['regular_price'];
            $product->sale_price = $pricing['sale_price'];

            $stock = 0;
            if ($product->relationLoaded('stocks')) {
                $stockRow = $product->stocks->first(function ($stk) {
                    return is_null($stk->variant_id) || $stk->variant_id == 0;
                });
                if ($stockRow) {
                    $stock = max(0, (int)$stockRow->quantity - (int)($stockRow->reserved_quantity ?? 0));
                }
            } else {
                $stock = $product->getBranchStock($branchId);
            }

            if ($stock <= 0 && ($isMainBranch || !isset($productsWithAnyStock[$product->id]))) {
                $stock = max(0, (int)($product->stock ?? ($product->total_stock ?? 0)));
            }
            $product->branch_stock = $stock;

            if ($product->is_variable && $product->relationLoaded('variants')) {
                foreach ($product->variants as $variant) {
                    $vPricing = BranchProductService::resolvePrice($product, $branchId, $variant->id);
                    $variant->regular_price = $vPricing['regular_price'];
                    $variant->sale_price = $vPricing['sale_price'];

                    $vStock = 0;
                    if ($product->relationLoaded('stocks')) {
                        $vStockRow = $product->stocks->first(function ($stk) use ($variant) {
                            return (int)$stk->variant_id === (int)$variant->id;
                        });
                        if ($vStockRow) {
                            $vStock = max(0, (int)$vStockRow->quantity - (int)($vStockRow->reserved_quantity ?? 0));
                        }
                    } else {
                        $vStock = $variant->getBranchStock($branchId);
                    }

                    if ($vStock <= 0 && ($isMainBranch || !isset($variantsWithAnyStock[$variant->id]))) {
                        $vStock = max(0, (int)($variant->stock ?? 0));
                    }
                    $variant->stock = $vStock;
                }
            }

            return $product;
        });

        $viewPath = view()->exists('admin.pos.partials.product_grid') 
            ? 'admin.pos.partials.product_grid' 
            : (view()->exists('admin.pos.product_grid') ? 'admin.pos.product_grid' : 'admin.pos.partials.product_grid');

        return view($viewPath, compact('products', 'branchId'));
    }

    /**
     * ৩. হাই-স্পিড বারকোড স্ক্যানার রেজোলিউশন (AJAX)
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode'   => 'required|string',
            'branch_id' => 'required|integer',
        ]);

        $branchId = (int) $request->branch_id;
        $barcode  = trim($request->barcode);

        if (!BranchAccessService::canAccessPos(Auth::user(), $branchId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized POS terminal access.'], 403);
        }

        try {
            $cartItem = BranchPOSService::scanBarcode($branchId, $barcode);
            return response()->json($cartItem);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * ৪. কাস্টমার লাইভ সার্চ (AJAX)
     */
    public function searchCustomers(Request $request)
    {
        $q = trim($request->get('q', ''));

        $selectCols = ['id', 'name', 'phone', 'email'];
        if (Schema::hasColumn('users', 'address')) {
            $selectCols[] = 'address';
        } elseif (Schema::hasColumn('users', 'bio')) {
            $selectCols[] = 'bio as address';
        }

        $query = User::select($selectCols);

        if (Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', true);
        } elseif (Schema::hasColumn('users', 'status')) {
            $query->where('status', 'active');
        }

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('phone', 'LIKE', "%{$q}%")
                    ->orWhere('name', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%");
            });
        }

        $customers = $query->limit(20)->get();

        return response()->json([
            'success'   => true,
            'customers' => $customers,
        ]);
    }

    /**
     * ৫. পিওএস টার্মিনাল থেকে দ্রুত নতুন কাস্টমার তৈরি (AJAX)
     */
    public function quickCustomer(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
        ]);

        try {
            $customer = BranchPOSService::quickCreateCustomer($request->all());

            return response()->json([
                'success'  => true,
                'message'  => 'Customer registered successfully.',
                'customer' => [
                    'id'      => $customer->id,
                    'name'    => $customer->name,
                    'phone'   => $customer->phone,
                    'address' => $customer->address ?? ($customer->bio ?? null),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    /**
     * ৬. অ্যাটোমিক পিওএস চেকআউট (Order Placement & Multi-Format Print URLs Sync)
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'branch_id'          => 'required|integer|exists:branches,id',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'payment_method'     => 'required|string|in:cash,bkash,nagad,rocket,card,bank_transfer',
            'paid_amount'        => 'required|numeric|min:0',
            'discount_amount'    => 'nullable|numeric|min:0',
            'tax_amount'         => 'nullable|numeric|min:0',
            'customer_id'        => 'nullable|integer',
            'sales_person_id'    => 'nullable|integer',
        ]);

        $branchId = (int) $request->branch_id;

        if (!BranchAccessService::canAccessPos(Auth::user(), $branchId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized POS terminal checkout.'], 403);
        }

        try {
            $response = BranchPOSService::processCheckout($branchId, $request->all(), Auth::user());
            $orderId = $response['order_id'];

            return response()->json([
                'success'       => true,
                'message'       => 'Order successfully placed!',
                'order_id'      => $orderId,
                'invoice_no'    => $response['invoice_no'],
                'grand_total'   => $response['grand_total'],
                'paid_amount'   => $response['paid_amount'],
                'due_amount'    => $response['due_amount'],
                'print_url'     => $response['print_url'],
                'memo_url'      => $response['memo_url'],
                // Multi-Format Direct Print URLs for Seamless Client-Side Autoprint
                'print_url_58'  => route('admin.pos.print.thermal58', $orderId),
                'print_url_80'  => route('admin.pos.print.thermal', $orderId),
                'print_url_a5'  => route('admin.pos.print.a5', $orderId),
                'print_url_a4'  => route('admin.pos.print.a4', $orderId),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * ৭. ৩-ইঞ্চি থার্মাল রিসিট প্রিন্ট ভিউ (POS Thermal Receipt 80mm)
     */
    public function printThermal($orderId)
    {
        $order = Order::withoutGlobalScopes()
            ->with(['branch', 'user', 'salesPerson', 'items.product', 'items.variant'])
            ->findOrFail($orderId);

        if (!BranchAccessService::canAccessBranch(Auth::user(), $order->branch_id)) {
            abort(403, 'Unauthorized invoice access.');
        }

        $branch = $order->branch;
        $view = view()->exists('admin.pos.print_thermal_80mm') 
            ? 'admin.pos.print_thermal_80mm' 
            : 'admin.pos.print_thermal';

        return view($view, compact('order', 'branch'));
    }

    /**
     * ৭.১ ২-ইঞ্চি থার্মাল রিসিট প্রিন্ট ভিউ (POS Thermal Receipt 58mm - Edge-to-Edge Full Width)
     */
    public function printThermal58($orderId)
    {
        $order = Order::withoutGlobalScopes()
            ->with(['branch', 'user', 'salesPerson', 'items.product', 'items.variant'])
            ->findOrFail($orderId);

        if (!BranchAccessService::canAccessBranch(Auth::user(), $order->branch_id)) {
            abort(403, 'Unauthorized invoice access.');
        }

        $branch = $order->branch;
        $view = view()->exists('admin.pos.print_thermal_58mm') 
            ? 'admin.pos.print_thermal_58mm' 
            : 'admin.pos.print_thermal';

        return view($view, compact('order', 'branch'));
    }

    /**
     * ৮. A5 অফিসিয়াল ইনভয়েস / ক্যাশ মেমো প্রিন্ট ভিউ
     */
    public function printA5($orderId)
    {
        $order = Order::withoutGlobalScopes()
            ->with(['branch', 'user', 'salesPerson', 'items.product', 'items.variant'])
            ->findOrFail($orderId);

        if (!BranchAccessService::canAccessBranch(Auth::user(), $order->branch_id)) {
            abort(403, 'Unauthorized invoice access.');
        }

        $branch = $order->branch;

        return view('admin.pos.print_a5', compact('order', 'branch'));
    }

    /**
     * ৮.১ A4 অফিসিয়াল ইনভয়েস / ক্যাশ মেমো প্রিন্ট ভিউ
     */
    public function printA4($orderId)
    {
        $order = Order::withoutGlobalScopes()
            ->with(['branch', 'user', 'salesPerson', 'items.product', 'items.variant'])
            ->findOrFail($orderId);

        if (!BranchAccessService::canAccessBranch(Auth::user(), $order->branch_id)) {
            abort(403, 'Unauthorized invoice access.');
        }

        $branch = $order->branch;
        $view = view()->exists('admin.pos.print_a4') 
            ? 'admin.pos.print_a4' 
            : 'admin.pos.print_a5';

        return view($view, compact('order', 'branch'));
    }

    /**
     * ৮.২ ইউনিভার্সাল পেপার ফরম্যাট রাউটার (Dynamic Fallback Resolver)
     */
    public function printReceipt($orderId, $format = '80mm')
    {
        return match(strtolower((string)$format)) {
            '58mm', '2inch', '2' => $this->printThermal58($orderId),
            'a4'                 => $this->printA4($orderId),
            'a5'                 => $this->printA5($orderId),
            default              => $this->printThermal($orderId),
        };
    }

    /**
     * ৯. রিয়েল-টাইম টার্মিনাল হেডার স্ট্যাটস (AJAX)
     */
    public function getStats(Request $request)
    {
        $branchId = (int) ($request->get('branch_id') ?: BranchAccessService::getCurrentBranchId());

        if (!$branchId || !BranchAccessService::canAccessBranch(Auth::user(), $branchId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $stats = BranchPOSService::getTodayPosStats($branchId);
        $closing = BranchReportService::calculateLiveSessionCash($branchId);

        return response()->json([
            'success'       => true,
            'stats'         => $stats,
            'expected_cash' => $closing['expected_cash'],
            'session_status'=> $closing['session_status'],
        ]);
    }
}
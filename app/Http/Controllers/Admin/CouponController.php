<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CouponController extends Controller
{
    /**
     * কুপন, ভাউচার ও রিডেম্পশন লগের সেন্ট্রাল ড্যাশবোর্ড (Enterprise Lifecycle & Real-time Auto-Sync)
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'coupons'); // 'coupons', 'vouchers', 'redemptions'

        // ১. coupon_voucher_redemptions টেবিলের কলাম স্বয়ংক্রিয়ভাবে ডিটেক্ট করা
        $redemptionCols = Schema::hasTable('coupon_voucher_redemptions')
            ? Schema::getColumnListing('coupon_voucher_redemptions')
            : [];

        $codeCol = collect(['code_used', 'coupon_code', 'code', 'voucher_code', 'promo_code'])->first(function ($c) use ($redemptionCols) {
            return in_array($c, $redemptionCols);
        });

        $hasCouponId = in_array('coupon_id', $redemptionCols);
        $hasVoucherId = in_array('voucher_id', $redemptionCols);
        $hasStatus = in_array('status', $redemptionCols);
        $typeCol = collect(['discount_type', 'type'])->first(function ($c) use ($redemptionCols) {
            return in_array($c, $redemptionCols);
        });
        $amountCol = collect(['discount_amount', 'discount', 'amount', 'value'])->first(function ($c) use ($redemptionCols) {
            return in_array($c, $redemptionCols);
        });

        // ২. রিয়েল-টাইম অটো-সিঙ্ক: ডাটাবেজের coupons টেবিলে used_count লাইভ রিডেম্পশন দিয়ে আপডেট করা
        if (Schema::hasTable('coupons') && !empty($redemptionCols)) {
            $allCoupons = DB::table('coupons')->get();
            foreach ($allCoupons as $cpn) {
                $countQuery = DB::table('coupon_voucher_redemptions');
                if ($hasStatus) {
                    $countQuery->whereIn('status', ['completed', 'applied', 'success']);
                }
                $countQuery->where(function ($match) use ($cpn, $redemptionCols, $codeCol, $hasCouponId) {
                    $matched = false;
                    if ($hasCouponId) {
                        $match->orWhere('coupon_id', $cpn->id);
                        $matched = true;
                    }
                    if ($codeCol) {
                        $match->orWhere($codeCol, $cpn->code);
                        $matched = true;
                    }
                    if (!$matched && in_array('code', $redemptionCols)) {
                        $match->orWhere('code', $cpn->code);
                    }
                });

                $realUsedCount = $countQuery->count();
                if (Schema::hasColumn('coupons', 'used_count')) {
                    DB::table('coupons')->where('id', $cpn->id)->update(['used_count' => $realUsedCount]);
                }
            }
        }

        // ৩. অটো-সিঙ্ক ভাউচার ক্লেইম কাউন্টার
        if (Schema::hasTable('vouchers') && Schema::hasTable('customer_vouchers')) {
            $voucherColumns = Schema::getColumnListing('vouchers');
            if (in_array('claimed_count', $voucherColumns) && Schema::hasColumn('customer_vouchers', 'voucher_id')) {
                $vCounts = DB::table('customer_vouchers')
                    ->select('voucher_id', DB::raw('count(*) as total_claimed'))
                    ->whereNotNull('voucher_id')
                    ->groupBy('voucher_id')
                    ->pluck('total_claimed', 'voucher_id');

                foreach ($vCounts as $vId => $vCount) {
                    DB::table('vouchers')->where('id', $vId)->update(['claimed_count' => $vCount]);
                }
            }
        }

        // ৪. কুপন তালিকা (রিয়েল-টাইম লাইভ সাব-কোয়েরিসহ)
        $coupons = collect();
        if (Schema::hasTable('coupons')) {
            $couponQuery = DB::table('coupons')->select('coupons.*');

            if (!empty($redemptionCols)) {
                $couponQuery->selectSub(function ($q) use ($codeCol, $hasCouponId, $hasStatus) {
                    $q->from('coupon_voucher_redemptions');
                    if ($hasStatus) {
                        $q->whereIn('coupon_voucher_redemptions.status', ['completed', 'applied', 'success']);
                    }
                    $q->where(function ($sub) use ($codeCol, $hasCouponId) {
                        $matched = false;
                        if ($hasCouponId) {
                            $sub->orWhereColumn('coupon_voucher_redemptions.coupon_id', 'coupons.id');
                            $matched = true;
                        }
                        if ($codeCol) {
                            $sub->orWhereColumn("coupon_voucher_redemptions.{$codeCol}", 'coupons.code');
                            $matched = true;
                        }
                    });
                    $q->selectRaw('count(*)');
                }, 'dynamic_used_count');

                if ($amountCol) {
                    $couponQuery->selectSub(function ($q) use ($codeCol, $hasCouponId, $hasStatus, $amountCol) {
                        $q->from('coupon_voucher_redemptions');
                        if ($hasStatus) {
                            $q->whereIn('coupon_voucher_redemptions.status', ['completed', 'applied', 'success']);
                        }
                        $q->where(function ($sub) use ($codeCol, $hasCouponId) {
                            $matched = false;
                            if ($hasCouponId) {
                                $sub->orWhereColumn('coupon_voucher_redemptions.coupon_id', 'coupons.id');
                                $matched = true;
                            }
                            if ($codeCol) {
                                $sub->orWhereColumn("coupon_voucher_redemptions.{$codeCol}", 'coupons.code');
                                $matched = true;
                            }
                        });
                        $q->selectRaw("COALESCE(SUM({$amountCol}), 0)");
                    }, 'dynamic_discount_disbursed');
                }
            }

            if ($request->filled('search_coupon')) {
                $search = trim($request->search_coupon);
                $couponQuery->where(function ($q) use ($search) {
                    $q->where('code', 'LIKE', "%{$search}%")
                      ->orWhere('name', 'LIKE', "%{$search}%");
                });
            }
            $coupons = $couponQuery->latest('id')->paginate(15, ['*'], 'coupons_page')->withQueryString();
        }

        // ৫. ভাউচার ক্যাম্পেইন তালিকা
        $vouchers = collect();
        if (Schema::hasTable('vouchers')) {
            $voucherQuery = DB::table('vouchers')->select('vouchers.*');

            if (Schema::hasTable('customer_vouchers') && Schema::hasColumn('customer_vouchers', 'voucher_id')) {
                $voucherQuery->selectSub(function ($q) {
                    $q->from('customer_vouchers')
                      ->whereColumn('customer_vouchers.voucher_id', 'vouchers.id')
                      ->selectRaw('count(*)');
                }, 'dynamic_claimed_count');
            }

            if (!empty($redemptionCols)) {
                $voucherQuery->selectSub(function ($q) use ($codeCol, $hasVoucherId, $typeCol, $hasStatus) {
                    $q->from('coupon_voucher_redemptions');
                    if ($typeCol) {
                        $q->where("coupon_voucher_redemptions.{$typeCol}", 'voucher');
                    }
                    if ($hasStatus) {
                        $q->whereIn('coupon_voucher_redemptions.status', ['completed', 'applied', 'success']);
                    }
                    if ($hasVoucherId) {
                        $q->whereColumn('coupon_voucher_redemptions.voucher_id', 'vouchers.id');
                    } elseif ($codeCol && Schema::hasTable('customer_vouchers') && Schema::hasColumn('customer_vouchers', 'voucher_code')) {
                        $q->whereIn("coupon_voucher_redemptions.{$codeCol}", function ($cv) {
                            $cv->from('customer_vouchers')
                               ->whereColumn('customer_vouchers.voucher_id', 'vouchers.id')
                               ->select('voucher_code');
                        });
                    }
                    $q->selectRaw('count(*)');
                }, 'dynamic_used_count');

                if ($amountCol) {
                    $voucherQuery->selectSub(function ($q) use ($codeCol, $hasVoucherId, $typeCol, $hasStatus, $amountCol) {
                        $q->from('coupon_voucher_redemptions');
                        if ($typeCol) {
                            $q->where("coupon_voucher_redemptions.{$typeCol}", 'voucher');
                        }
                        if ($hasStatus) {
                            $q->whereIn('coupon_voucher_redemptions.status', ['completed', 'applied', 'success']);
                        }
                        if ($hasVoucherId) {
                            $q->whereColumn('coupon_voucher_redemptions.voucher_id', 'vouchers.id');
                        } elseif ($codeCol && Schema::hasTable('customer_vouchers') && Schema::hasColumn('customer_vouchers', 'voucher_code')) {
                            $q->whereIn("coupon_voucher_redemptions.{$codeCol}", function ($cv) {
                                $cv->from('customer_vouchers')
                                   ->whereColumn('customer_vouchers.voucher_id', 'vouchers.id')
                                   ->select('voucher_code');
                            });
                        }
                        $q->selectRaw("COALESCE(SUM({$amountCol}), 0)");
                    }, 'dynamic_discount_disbursed');
                }
            }

            if ($request->filled('search_voucher')) {
                $search = trim($request->search_voucher);
                $voucherQuery->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%");
                    if (Schema::hasColumn('vouchers', 'code_prefix')) {
                        $q->orWhere('code_prefix', 'LIKE', "%{$search}%");
                    }
                });
            }
            $vouchers = $voucherQuery->latest('id')->paginate(15, ['*'], 'vouchers_page')->withQueryString();
        }

        // ৬. রিডেম্পশন হিস্ট্রি অডিট লগ (Safe Property Mapping via through)
        $redemptions = collect();
        if (Schema::hasTable('coupon_voucher_redemptions')) {
            $redemptionsQuery = DB::table('coupon_voucher_redemptions');
            if (Schema::hasTable('orders')) {
                $redemptionsQuery->leftJoin('orders', 'coupon_voucher_redemptions.order_id', '=', 'orders.id');
            }
            if (Schema::hasTable('users')) {
                $redemptionsQuery->leftJoin('users', 'coupon_voucher_redemptions.user_id', '=', 'users.id');
            }

            $selects = ['coupon_voucher_redemptions.*'];
            if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'invoice_no')) {
                $selects[] = 'orders.invoice_no';
            }
            if (Schema::hasTable('users')) {
                if (Schema::hasColumn('users', 'name')) $selects[] = 'users.name as user_name';
                if (Schema::hasColumn('users', 'phone')) $selects[] = 'users.phone as user_phone';
            }

            $redemptions = $redemptionsQuery
                ->select($selects)
                ->latest('coupon_voucher_redemptions.id')
                ->paginate(20, ['*'], 'redemptions_page')
                ->through(function ($r) use ($codeCol, $typeCol, $amountCol) {
                    $r->code_used = $codeCol && isset($r->$codeCol)
                        ? $r->$codeCol
                        : ($r->code_used ?? ($r->coupon_code ?? ($r->code ?? ($r->voucher_code ?? ($r->promo_code ?? 'N/A')))));

                    $r->discount_type = $typeCol && isset($r->$typeCol)
                        ? $r->$typeCol
                        : ($r->discount_type ?? ($r->type ?? 'coupon'));

                    $r->discount_amount = $amountCol && isset($r->$amountCol)
                        ? (float)$r->$amountCol
                        : (float)($r->discount_amount ?? ($r->discount ?? ($r->amount ?? ($r->value ?? 0))));

                    $r->status = $r->status ?? 'completed';

                    return $r;
                })
                ->withQueryString();
        }

        // ৭. অ্যানালিটিক্স মেট্রিক্স (Active Completed vs Voided/Restored Separated)
        $totalActiveRedemptions = 0;
        $totalVoidedRedemptions = 0;
        $totalDiscountGiven = 0.0;
        if (!empty($redemptionCols)) {
            $rQuery = DB::table('coupon_voucher_redemptions');
            if ($hasStatus) {
                $totalActiveRedemptions = (clone $rQuery)->whereIn('status', ['completed', 'applied', 'success'])->count();
                $totalVoidedRedemptions = (clone $rQuery)->whereIn('status', ['restored', 'void', 'voided', 'cancelled'])->count();
                if ($amountCol) {
                    $totalDiscountGiven = (float)(clone $rQuery)->whereIn('status', ['completed', 'applied', 'success'])->sum($amountCol);
                }
            } else {
                $totalActiveRedemptions = (clone $rQuery)->count();
                if ($amountCol) {
                    $totalDiscountGiven = (float)(clone $rQuery)->sum($amountCol);
                }
            }
        }

        $metrics = [
            'total_coupons'            => Schema::hasTable('coupons') ? DB::table('coupons')->count() : 0,
            'active_coupons'           => Schema::hasTable('coupons') ? DB::table('coupons')->where('is_active', 1)->count() : 0,
            'total_vouchers'           => Schema::hasTable('vouchers') ? DB::table('vouchers')->count() : 0,
            'active_vouchers'          => Schema::hasTable('vouchers') ? DB::table('vouchers')->where('is_active', 1)->count() : 0,
            'total_redemptions'        => $totalActiveRedemptions,
            'total_voided_redemptions' => $totalVoidedRedemptions,
            'total_discount_given'     => $totalDiscountGiven,
        ];

        return view('admin.promo.coupons.index', compact('coupons', 'vouchers', 'redemptions', 'metrics', 'tab'));
    }

    /**
     * কুপন তৈরির ফর্ম (Preloads Products & Categories with SKU, Image & Price)
     */
    public function createCoupon()
    {
        $products = collect();
        if (Schema::hasTable('products')) {
            $pCols = Schema::getColumnListing('products');
            $pSelect = ['id'];
            $pSelect[] = in_array('title', $pCols) ? 'title' : (in_array('name', $pCols) ? 'name as title' : 'id as title');
            if (in_array('sku', $pCols)) $pSelect[] = 'sku';
            if (in_array('regular_price', $pCols)) $pSelect[] = 'regular_price';
            elseif (in_array('price', $pCols)) $pSelect[] = 'price as regular_price';
            if (in_array('image', $pCols)) $pSelect[] = 'image';
            $products = DB::table('products')->select($pSelect)->take(150)->get();
        }

        $categories = collect();
        if (Schema::hasTable('categories')) {
            $cCols = Schema::getColumnListing('categories');
            $cSelect = ['id'];
            $cSelect[] = in_array('name', $cCols) ? 'name' : (in_array('title', $cCols) ? 'title as name' : 'id as name');
            if (in_array('slug', $cCols)) $cSelect[] = 'slug';
            $categories = DB::table('categories')->select($cSelect)->get();
        }

        return view('admin.promo.coupons.create_coupon', compact('products', 'categories'));
    }

    /**
     * কুপন সংরক্ষণ (Supports Target User Type & Product/Category Multiple Selection)
     */
    public function storeCoupon(Request $request)
    {
        $request->validate([
            'code'                => 'required|string|max:50|unique:coupons,code',
            'name'                => 'nullable|string|max:255',
            'discount_type'       => 'required|in:fixed,percentage,free_shipping',
            'discount_amount'     => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'total_usage_limit'   => 'nullable|integer|min:1',
            'per_user_limit'      => 'nullable|integer|min:1',
            'target_user_type'    => 'nullable|in:all,registered_only,guest_only',
            'start_date'          => 'nullable|date',
            'expire_date'         => 'nullable|date|after_or_equal:start_date',
            'applicable_to'       => 'required|in:all,specific_products,specific_categories',
        ]);

        DB::transaction(function () use ($request) {
            $couponColumns = Schema::getColumnListing('coupons');

            $rawCouponData = [
                'code'                 => strtoupper(trim($request->code)),
                'name'                 => $request->name,
                'description'          => $request->description,
                'customer_description' => $request->customer_description,
                'discount_type'        => $request->discount_type,
                'type'                 => $request->discount_type,
                'discount_amount'      => (float)$request->discount_amount,
                'value'                => (float)$request->discount_amount,
                'max_discount_amount'  => $request->filled('max_discount_amount') ? (float)$request->max_discount_amount : null,
                'max_discount'         => $request->filled('max_discount_amount') ? (float)$request->max_discount_amount : null,
                'min_order_amount'     => (float)($request->min_order_amount ?? 0),
                'min_spend'            => (float)($request->min_order_amount ?? 0),
                'max_order_amount'     => $request->filled('max_order_amount') ? (float)$request->max_order_amount : null,
                'total_usage_limit'    => $request->filled('total_usage_limit') ? (int)$request->total_usage_limit : null,
                'usage_limit'          => $request->filled('total_usage_limit') ? (int)$request->total_usage_limit : null,
                'used_count'           => 0,
                'per_user_limit'       => (int)($request->per_user_limit ?? 1),
                'usage_limit_per_user' => (int)($request->per_user_limit ?? 1),
                'target_user_type'     => $request->target_user_type ?? 'all',
                'start_date'           => $request->start_date ? Carbon::parse($request->start_date) : null,
                'expire_date'          => $request->expire_date ? Carbon::parse($request->expire_date)->endOfDay() : null,
                'expires_at'           => $request->expire_date ? Carbon::parse($request->expire_date)->endOfDay() : null,
                'is_active'            => $request->has('is_active') ? 1 : 0,
                'status'               => $request->has('is_active') ? 1 : 0,
                'is_public'            => $request->has('is_public') ? 1 : 0,
                'is_stackable'         => $request->has('is_stackable') ? 1 : 0,
                'applicable_to'        => $request->applicable_to,
                'badge_text'           => $request->badge_text,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];

            $insertData = array_intersect_key($rawCouponData, array_flip($couponColumns));
            $couponId = DB::table('coupons')->insertGetId($insertData);

            // Handle specific products pivot / json
            if ($request->applicable_to === 'specific_products' && $request->has('product_ids')) {
                $productIds = is_array($request->product_ids) ? $request->product_ids : explode(',', $request->product_ids);
                if (Schema::hasTable('coupon_products')) {
                    foreach ($productIds as $pId) {
                        if (!empty($pId)) {
                            DB::table('coupon_products')->insert([
                                'coupon_id'   => $couponId,
                                'product_id'  => (int)$pId,
                                'is_excluded' => 0,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        }
                    }
                } elseif (in_array('product_ids', $couponColumns)) {
                    DB::table('coupons')->where('id', $couponId)->update(['product_ids' => json_encode($productIds)]);
                }
            }

            // Handle specific categories pivot / json
            if ($request->applicable_to === 'specific_categories' && $request->has('category_ids')) {
                $categoryIds = is_array($request->category_ids) ? $request->category_ids : explode(',', $request->category_ids);
                if (Schema::hasTable('coupon_categories')) {
                    foreach ($categoryIds as $cId) {
                        if (!empty($cId)) {
                            DB::table('coupon_categories')->insert([
                                'coupon_id'   => $couponId,
                                'category_id' => (int)$cId,
                                'is_excluded' => 0,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        }
                    }
                } elseif (in_array('category_ids', $couponColumns)) {
                    DB::table('coupons')->where('id', $couponId)->update(['category_ids' => json_encode($categoryIds)]);
                }
            }
        });

        return redirect('/admin/promo/coupons?tab=coupons')->with('success', 'Coupon created successfully!');
    }

    /**
     * কুপন এডিট ফর্ম
     */
    public function editCoupon($id)
    {
        $coupon = DB::table('coupons')->where('id', $id)->firstOrFail();

        $products = collect();
        if (Schema::hasTable('products')) {
            $pCols = Schema::getColumnListing('products');
            $pSelect = ['id'];
            $pSelect[] = in_array('title', $pCols) ? 'title' : (in_array('name', $pCols) ? 'name as title' : 'id as title');
            if (in_array('sku', $pCols)) $pSelect[] = 'sku';
            if (in_array('regular_price', $pCols)) $pSelect[] = 'regular_price';
            elseif (in_array('price', $pCols)) $pSelect[] = 'price as regular_price';
            if (in_array('image', $pCols)) $pSelect[] = 'image';
            $products = DB::table('products')->select($pSelect)->take(150)->get();
        }

        $categories = collect();
        if (Schema::hasTable('categories')) {
            $cCols = Schema::getColumnListing('categories');
            $cSelect = ['id'];
            $cSelect[] = in_array('name', $cCols) ? 'name' : (in_array('title', $cCols) ? 'title as name' : 'id as name');
            if (in_array('slug', $cCols)) $cSelect[] = 'slug';
            $categories = DB::table('categories')->select($cSelect)->get();
        }

        $selectedProducts = [];
        if (Schema::hasTable('coupon_products')) {
            $selectedProducts = DB::table('coupon_products')->where('coupon_id', $id)->pluck('product_id')->toArray();
        } elseif (!empty($coupon->product_ids)) {
            $selectedProducts = is_array($coupon->product_ids) ? $coupon->product_ids : (json_decode($coupon->product_ids, true) ?? []);
        }

        $selectedCategories = [];
        if (Schema::hasTable('coupon_categories')) {
            $selectedCategories = DB::table('coupon_categories')->where('coupon_id', $id)->pluck('category_id')->toArray();
        } elseif (!empty($coupon->category_ids)) {
            $selectedCategories = is_array($coupon->category_ids) ? $coupon->category_ids : (json_decode($coupon->category_ids, true) ?? []);
        }

        return view('admin.promo.coupons.edit_coupon', compact('coupon', 'products', 'categories', 'selectedProducts', 'selectedCategories'));
    }

    /**
     * কুপন আপডেট
     */
    public function updateCoupon(Request $request, $id)
    {
        $request->validate([
            'code'                => 'required|string|max:50|unique:coupons,code,' . $id,
            'name'                => 'nullable|string|max:255',
            'discount_type'       => 'required|in:fixed,percentage,free_shipping',
            'discount_amount'     => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'total_usage_limit'   => 'nullable|integer|min:1',
            'per_user_limit'      => 'nullable|integer|min:1',
            'target_user_type'    => 'nullable|in:all,registered_only,guest_only',
            'start_date'          => 'nullable|date',
            'expire_date'         => 'nullable|date',
            'applicable_to'       => 'required|in:all,specific_products,specific_categories',
        ]);

        DB::transaction(function () use ($request, $id) {
            $couponColumns = Schema::getColumnListing('coupons');

            $rawUpdateData = [
                'code'                 => strtoupper(trim($request->code)),
                'name'                 => $request->name,
                'description'          => $request->description,
                'customer_description' => $request->customer_description,
                'discount_type'        => $request->discount_type,
                'type'                 => $request->discount_type,
                'discount_amount'      => (float)$request->discount_amount,
                'value'                => (float)$request->discount_amount,
                'max_discount_amount'  => $request->filled('max_discount_amount') ? (float)$request->max_discount_amount : null,
                'max_discount'         => $request->filled('max_discount_amount') ? (float)$request->max_discount_amount : null,
                'min_order_amount'     => (float)($request->min_order_amount ?? 0),
                'min_spend'            => (float)($request->min_order_amount ?? 0),
                'max_order_amount'     => $request->filled('max_order_amount') ? (float)$request->max_order_amount : null,
                'total_usage_limit'    => $request->filled('total_usage_limit') ? (int)$request->total_usage_limit : null,
                'usage_limit'          => $request->filled('total_usage_limit') ? (int)$request->total_usage_limit : null,
                'per_user_limit'       => (int)($request->per_user_limit ?? 1),
                'usage_limit_per_user' => (int)($request->per_user_limit ?? 1),
                'target_user_type'     => $request->target_user_type ?? 'all',
                'start_date'           => $request->start_date ? Carbon::parse($request->start_date) : null,
                'expire_date'          => $request->expire_date ? Carbon::parse($request->expire_date)->endOfDay() : null,
                'expires_at'           => $request->expire_date ? Carbon::parse($request->expire_date)->endOfDay() : null,
                'is_active'            => $request->has('is_active') ? 1 : 0,
                'status'               => $request->has('is_active') ? 1 : 0,
                'is_public'            => $request->has('is_public') ? 1 : 0,
                'is_stackable'         => $request->has('is_stackable') ? 1 : 0,
                'applicable_to'        => $request->applicable_to,
                'badge_text'           => $request->badge_text,
                'updated_at'           => now(),
            ];

            $updateData = array_intersect_key($rawUpdateData, array_flip($couponColumns));
            DB::table('coupons')->where('id', $id)->update($updateData);

            // Handle Products
            if (Schema::hasTable('coupon_products')) {
                DB::table('coupon_products')->where('coupon_id', $id)->delete();
                if ($request->applicable_to === 'specific_products' && $request->has('product_ids')) {
                    $productIds = is_array($request->product_ids) ? $request->product_ids : explode(',', $request->product_ids);
                    foreach ($productIds as $pId) {
                        if (!empty($pId)) {
                            DB::table('coupon_products')->insert([
                                'coupon_id'   => $id,
                                'product_id'  => (int)$pId,
                                'is_excluded' => 0,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        }
                    }
                }
            } elseif (in_array('product_ids', $couponColumns)) {
                $productIds = ($request->applicable_to === 'specific_products' && $request->has('product_ids'))
                    ? (is_array($request->product_ids) ? $request->product_ids : explode(',', $request->product_ids))
                    : [];
                DB::table('coupons')->where('id', $id)->update(['product_ids' => json_encode($productIds)]);
            }

            // Handle Categories
            if (Schema::hasTable('coupon_categories')) {
                DB::table('coupon_categories')->where('coupon_id', $id)->delete();
                if ($request->applicable_to === 'specific_categories' && $request->has('category_ids')) {
                    $categoryIds = is_array($request->category_ids) ? $request->category_ids : explode(',', $request->category_ids);
                    foreach ($categoryIds as $cId) {
                        if (!empty($cId)) {
                            DB::table('coupon_categories')->insert([
                                'coupon_id'   => $id,
                                'category_id' => (int)$cId,
                                'is_excluded' => 0,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        }
                    }
                }
            } elseif (in_array('category_ids', $couponColumns)) {
                $categoryIds = ($request->applicable_to === 'specific_categories' && $request->has('category_ids'))
                    ? (is_array($request->category_ids) ? $request->category_ids : explode(',', $request->category_ids))
                    : [];
                DB::table('coupons')->where('id', $id)->update(['category_ids' => json_encode($categoryIds)]);
            }
        });

        return redirect('/admin/promo/coupons?tab=coupons')->with('success', 'Coupon updated successfully!');
    }

    /**
     * কুপন ডিলিট
     */
    public function destroyCoupon($id)
    {
        DB::table('coupons')->where('id', $id)->delete();
        if (Schema::hasTable('coupon_products')) DB::table('coupon_products')->where('coupon_id', $id)->delete();
        if (Schema::hasTable('coupon_categories')) DB::table('coupon_categories')->where('coupon_id', $id)->delete();
        if (Schema::hasTable('coupon_users')) DB::table('coupon_users')->where('coupon_id', $id)->delete();

        return back()->with('success', 'Coupon deleted successfully.');
    }

    /**
     * কুপন অ্যাক্টিভ/ইনঅ্যাক্টিভ টগল
     */
    public function toggleCouponStatus($id)
    {
        $coupon = DB::table('coupons')->where('id', $id)->first();
        if (!$coupon) return response()->json(['success' => false, 'message' => 'Coupon not found.'], 404);

        $newStatus = $coupon->is_active ? 0 : 1;
        $couponColumns = Schema::getColumnListing('coupons');
        $updatePayload = ['updated_at' => now()];
        if (in_array('is_active', $couponColumns)) $updatePayload['is_active'] = $newStatus;
        if (in_array('status', $couponColumns)) $updatePayload['status'] = $newStatus;

        DB::table('coupons')->where('id', $id)->update($updatePayload);

        return response()->json(['success' => true, 'is_active' => $newStatus, 'message' => 'Status updated.']);
    }

    /**
     * ভাউচার ক্যাম্পেইন তৈরির ফর্ম
     */
    public function createVoucher()
    {
        return view('admin.promo.coupons.create_voucher');
    }

    /**
     * ভাউচার ক্যাম্পেইন সংরক্ষণ
     */
    public function storeVoucher(Request $request)
    {
        $request->validate([
            'title'                => 'required|string|max:255',
            'code_prefix'          => 'nullable|string|max:20',
            'voucher_type'         => 'required|in:fixed,percentage,free_shipping',
            'discount_amount'      => 'required|numeric|min:0',
            'max_discount'         => 'nullable|numeric|min:0',
            'min_spend'            => 'nullable|numeric|min:0',
            'validity_days'        => 'required|integer|min:1',
            'total_vouchers_count' => 'nullable|integer|min:1',
            'max_claims_per_user'  => 'required|integer|min:1',
            'target_user_type'     => 'nullable|in:all,registered_only,guest_only',
            'claim_start'          => 'nullable|date',
            'claim_end'            => 'nullable|date|after_or_equal:claim_start',
        ]);

        $voucherColumns = Schema::getColumnListing('vouchers');

        $rawVoucherData = [
            'title'                => $request->title,
            'name'                 => $request->title,
            'code_prefix'          => strtoupper(trim($request->code_prefix ?? 'VCH')),
            'prefix'               => strtoupper(trim($request->code_prefix ?? 'VCH')),
            'voucher_type'         => $request->voucher_type,
            'type'                 => $request->voucher_type,
            'discount_amount'      => (float)$request->discount_amount,
            'value'                => (float)$request->discount_amount,
            'max_discount'         => $request->filled('max_discount') ? (float)$request->max_discount : null,
            'min_spend'            => (float)($request->min_spend ?? 0),
            'validity_days'        => (int)$request->validity_days,
            'total_vouchers_count' => $request->filled('total_vouchers_count') ? (int)$request->total_vouchers_count : null,
            'claimed_count'        => 0,
            'max_claims_per_user'  => (int)$request->max_claims_per_user,
            'target_user_type'     => $request->target_user_type ?? 'registered_only',
            'claim_start'          => $request->claim_start ? Carbon::parse($request->claim_start) : null,
            'claim_end'            => $request->claim_end ? Carbon::parse($request->claim_end)->endOfDay() : null,
            'is_active'            => $request->has('is_active') ? 1 : 0,
            'status'               => $request->has('is_active') ? 1 : 0,
            'terms_conditions'     => $request->terms_conditions,
            'created_at'           => now(),
            'updated_at'           => now(),
        ];

        $insertData = array_intersect_key($rawVoucherData, array_flip($voucherColumns));
        DB::table('vouchers')->insert($insertData);

        return redirect('/admin/promo/coupons?tab=vouchers')->with('success', 'Voucher campaign created successfully!');
    }

    /**
     * ভাউচার ক্যাম্পেইন এডিট ফর্ম
     */
    public function editVoucher($id)
    {
        $voucher = DB::table('vouchers')->where('id', $id)->firstOrFail();
        return view('admin.promo.coupons.edit_voucher', compact('voucher'));
    }

    /**
     * ভাউচার ক্যাম্পেইন আপডেট
     */
    public function updateVoucher(Request $request, $id)
    {
        $request->validate([
            'title'                => 'required|string|max:255',
            'code_prefix'          => 'nullable|string|max:20',
            'voucher_type'         => 'required|in:fixed,percentage,free_shipping',
            'discount_amount'      => 'required|numeric|min:0',
            'max_discount'         => 'nullable|numeric|min:0',
            'min_spend'            => 'nullable|numeric|min:0',
            'validity_days'        => 'required|integer|min:1',
            'total_vouchers_count' => 'nullable|integer|min:1',
            'max_claims_per_user'  => 'required|integer|min:1',
            'target_user_type'     => 'nullable|in:all,registered_only,guest_only',
            'claim_start'          => 'nullable|date',
            'claim_end'            => 'nullable|date',
        ]);

        $voucherColumns = Schema::getColumnListing('vouchers');

        $rawUpdateData = [
            'title'                => $request->title,
            'name'                 => $request->title,
            'code_prefix'          => strtoupper(trim($request->code_prefix ?? 'VCH')),
            'prefix'               => strtoupper(trim($request->code_prefix ?? 'VCH')),
            'voucher_type'         => $request->voucher_type,
            'type'                 => $request->voucher_type,
            'discount_amount'      => (float)$request->discount_amount,
            'value'                => (float)$request->discount_amount,
            'max_discount'         => $request->filled('max_discount') ? (float)$request->max_discount : null,
            'min_spend'            => (float)($request->min_spend ?? 0),
            'validity_days'        => (int)$request->validity_days,
            'total_vouchers_count' => $request->filled('total_vouchers_count') ? (int)$request->total_vouchers_count : null,
            'max_claims_per_user'  => (int)$request->max_claims_per_user,
            'target_user_type'     => $request->target_user_type ?? 'registered_only',
            'claim_start'          => $request->claim_start ? Carbon::parse($request->claim_start) : null,
            'claim_end'            => $request->claim_end ? Carbon::parse($request->claim_end)->endOfDay() : null,
            'is_active'            => $request->has('is_active') ? 1 : 0,
            'status'               => $request->has('is_active') ? 1 : 0,
            'terms_conditions'     => $request->terms_conditions,
            'updated_at'           => now(),
        ];

        $updateData = array_intersect_key($rawUpdateData, array_flip($voucherColumns));
        DB::table('vouchers')->where('id', $id)->update($updateData);

        return redirect('/admin/promo/coupons?tab=vouchers')->with('success', 'Voucher campaign updated successfully!');
    }

    /**
     * ভাউচার ক্যাম্পেইন ডিলিট
     */
    public function destroyVoucher($id)
    {
        DB::table('vouchers')->where('id', $id)->delete();
        return back()->with('success', 'Voucher campaign deleted successfully.');
    }

    /**
     * ভাউচার অ্যাক্টিভ/ইনঅ্যাক্টিভ টগল
     */
    public function toggleVoucherStatus($id)
    {
        $voucher = DB::table('vouchers')->where('id', $id)->first();
        if (!$voucher) return response()->json(['success' => false, 'message' => 'Voucher not found.'], 404);

        $newStatus = $voucher->is_active ? 0 : 1;
        $voucherColumns = Schema::getColumnListing('vouchers');
        $updatePayload = ['updated_at' => now()];
        if (in_array('is_active', $voucherColumns)) $updatePayload['is_active'] = $newStatus;
        if (in_array('status', $voucherColumns)) $updatePayload['status'] = $newStatus;

        DB::table('vouchers')->where('id', $id)->update($updatePayload);

        return response()->json(['success' => true, 'is_active' => $newStatus, 'message' => 'Status updated.']);
    }

    /**
     * রিডেম্পশন ভয়েড / রিস্টোর লাইফসাইকেল হ্যান্ডলার (Void & Restore action - Supports both GET & POST)
     */
    public function toggleRedemptionStatus($id)
    {
        if (!Schema::hasTable('coupon_voucher_redemptions')) {
            return redirect('/admin/promo/coupons?tab=redemptions')->with('error', 'Redemption table not found.');
        }

        $redemption = DB::table('coupon_voucher_redemptions')->where('id', $id)->first();
        if (!$redemption) {
            return redirect('/admin/promo/coupons?tab=redemptions')->with('error', 'Redemption record not found.');
        }

        $redemptionCols = Schema::getColumnListing('coupon_voucher_redemptions');
        $codeCol = collect(['code_used', 'coupon_code', 'code', 'voucher_code', 'promo_code'])->first(function ($c) use ($redemptionCols) {
            return in_array($c, $redemptionCols);
        });
        $usedCode = $codeCol ? ($redemption->$codeCol ?? null) : null;
        $typeCol = collect(['discount_type', 'type'])->first(function ($c) use ($redemptionCols) {
            return in_array($c, $redemptionCols);
        });
        $discType = $typeCol ? ($redemption->$typeCol ?? 'coupon') : 'coupon';

        $currentStatus = $redemption->status ?? 'completed';
        $newStatus = $currentStatus === 'completed' ? 'restored' : 'completed';

        DB::transaction(function () use ($redemption, $id, $newStatus, $usedCode, $discType, $codeCol, $redemptionCols) {
            DB::table('coupon_voucher_redemptions')->where('id', $id)->update([
                'status'     => $newStatus,
                'updated_at' => now(),
            ]);

            // ভাউচার হলে কাস্টমার ওয়ালেটে ভাউচারটি আবার ফেরত (Active/Claimed) দেওয়া
            if ($discType === 'voucher' && Schema::hasTable('customer_vouchers') && $usedCode) {
                DB::table('customer_vouchers')
                    ->where('voucher_code', $usedCode)
                    ->update([
                        'status'     => ($newStatus === 'restored' ? 'claimed' : 'used'),
                        'updated_at' => now(),
                    ]);
            }

            // কুপন হলে কুপন টেবিলের used_count সরাসরি লাইভ কাউন্ট দিয়ে সিঙ্ক করা
            if (Schema::hasTable('coupons') && Schema::hasColumn('coupons', 'used_count')) {
                $couponMatch = DB::table('coupons');
                if ($usedCode) {
                    $couponMatch->where('code', $usedCode);
                } elseif (isset($redemption->coupon_id)) {
                    $couponMatch->where('id', $redemption->coupon_id);
                }
                $targetCoupon = $couponMatch->first();

                if ($targetCoupon) {
                    $countQ = DB::table('coupon_voucher_redemptions')
                        ->whereIn('status', ['completed', 'applied', 'success']);

                    $countQ->where(function ($m) use ($targetCoupon, $redemptionCols, $codeCol) {
                        $done = false;
                        if (in_array('coupon_id', $redemptionCols)) {
                            $m->orWhere('coupon_id', $targetCoupon->id);
                            $done = true;
                        }
                        if ($codeCol) {
                            $m->orWhere($codeCol, $targetCoupon->code);
                            $done = true;
                        }
                    });

                    $realCount = $countQ->count();
                    DB::table('coupons')->where('id', $targetCoupon->id)->update(['used_count' => $realCount]);
                }
            }
        });

        $msg = $newStatus === 'restored' 
            ? 'Discount redemption marked as restored/voided. Quota restored successfully!' 
            : 'Discount redemption marked as completed.';

        return redirect('/admin/promo/coupons?tab=redemptions')->with('success', $msg);
    }

    /**
     * AJAX Search for Products (Search by Title, SKU, or ID)
     */
    public function searchProductsAjax(Request $request)
    {
        if (!Schema::hasTable('products')) {
            return response()->json([]);
        }

        $term = trim($request->q ?? '');
        $pCols = Schema::getColumnListing('products');
        $query = DB::table('products');

        if (!empty($term)) {
            $query->where(function ($q) use ($term, $pCols) {
                $q->where('id', $term);
                if (in_array('title', $pCols)) $q->orWhere('title', 'LIKE', "%{$term}%");
                if (in_array('name', $pCols)) $q->orWhere('name', 'LIKE', "%{$term}%");
                if (in_array('sku', $pCols)) $q->orWhere('sku', 'LIKE', "%{$term}%");
            });
        }

        $pSelect = ['id'];
        $pSelect[] = in_array('title', $pCols) ? 'title' : (in_array('name', $pCols) ? 'name as title' : 'id as title');
        if (in_array('sku', $pCols)) $pSelect[] = 'sku';
        if (in_array('regular_price', $pCols)) $pSelect[] = 'regular_price';
        elseif (in_array('price', $pCols)) $pSelect[] = 'price as regular_price';
        if (in_array('image', $pCols)) $pSelect[] = 'image';

        $items = $query->select($pSelect)->take(30)->get();

        return response()->json($items);
    }

    /**
     * AJAX Search for Categories (Search by Name or Slug)
     */
    public function searchCategoriesAjax(Request $request)
    {
        if (!Schema::hasTable('categories')) {
            return response()->json([]);
        }

        $term = trim($request->q ?? '');
        $cCols = Schema::getColumnListing('categories');
        $query = DB::table('categories');

        if (!empty($term)) {
            $query->where(function ($q) use ($term, $cCols) {
                $q->where('id', $term);
                if (in_array('name', $cCols)) $q->orWhere('name', 'LIKE', "%{$term}%");
                if (in_array('slug', $cCols)) $q->orWhere('slug', 'LIKE', "%{$term}%");
            });
        }

        $cSelect = ['id'];
        $cSelect[] = in_array('name', $cCols) ? 'name' : (in_array('title', $cCols) ? 'title as name' : 'id as name');
        if (in_array('slug', $cCols)) $cSelect[] = 'slug';

        $items = $query->select($cSelect)->take(30)->get();

        return response()->json($items);
    }
}
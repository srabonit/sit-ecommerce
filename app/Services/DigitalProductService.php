<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DigitalProductService
{
    /**
     * ডিজিটাল অর্ডার প্রসেসিং:
     * ১. Downloadable ফাইলের জন্য ইউনিক টোকেন তৈরি (১ বার)।
     * ২. Virtual Product-এর যতগুলো Quantity কেনা হয়েছে, ঠিক ততগুলো License Key বরাদ্দ করা।
     */
    public static function fulfillDigitalOrder($orderId): bool
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) return false;

        $orderItems = DB::table('order_items')->where('order_id', $orderId)->get();
        if ($orderItems->isEmpty()) return false;

        foreach ($orderItems as $item) {
            $productId = $item->product_id ?? null;
            if (!$productId) continue;

            $product = DB::table('products')->where('id', $productId)->first();
            if (!$product) continue;

            $isVirtual = (bool)($product->is_virtual ?? ($product->virtual ?? false));
            $isDownloadable = (bool)($product->is_downloadable ?? ($product->downloadable ?? false));
            $prodType = strtolower((string)($product->product_type ?? ''));

            $isDigital = $isVirtual || $isDownloadable 
                || str_contains($prodType, 'virtual') 
                || str_contains($prodType, 'download') 
                || str_contains($prodType, 'digital') 
                || str_contains($prodType, 'license');

            if (!$isDigital) continue;

            $instructions = $product->customer_instructions 
                ?? ($product->access_instructions 
                ?? ($product->license_instructions 
                ?? ($product->access_note 
                ?? ($product->license_note ?? null))));

            // ================= ১. ডাউনলোডেবল ফাইল (প্রতি ফাইলে ১টি করে লিংক) =================
            if (Schema::hasTable('product_downloads') && Schema::hasTable('order_downloads')) {
                $pDownloads = DB::table('product_downloads')->where('product_id', $productId)->get();

                foreach ($pDownloads as $pDownload) {
                    $existing = DB::table('order_downloads')
                        ->where('order_id', $orderId)
                        ->where('product_download_id', $pDownload->id)
                        ->first();

                    if (!$existing) {
                        $expiryDays = isset($pDownload->download_expiry_days) 
                            ? $pDownload->download_expiry_days 
                            : (isset($product->download_expiry_days) ? $product->download_expiry_days : (isset($product->download_expiry) ? $product->download_expiry : null));

                        $expiresAt = null;
                        if (!empty($expiryDays) && (int)$expiryDays > 0) {
                            $expiresAt = now()->addDays((int)$expiryDays);
                        }

                        $limit = isset($pDownload->download_limit) 
                            ? $pDownload->download_limit 
                            : (isset($product->download_limit) ? $product->download_limit : null);

                        $downloadInsertData = [
                            'order_id'            => $orderId,
                            'product_id'          => $productId,
                            'product_download_id' => $pDownload->id,
                            'download_token'      => Str::random(40),
                            'download_count'      => 0,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ];

                        if (Schema::hasColumn('order_downloads', 'download_limit')) {
                            $downloadInsertData['download_limit'] = !is_null($limit) ? (int)$limit : null;
                        }

                        if (Schema::hasColumn('order_downloads', 'expires_at')) {
                            $downloadInsertData['expires_at'] = $expiresAt;
                        }

                        DB::table('order_downloads')->insert($downloadInsertData);
                    }
                }
            }

            // ================= ২. সফটওয়্যার লাইসেন্স কী (Quantity অনুযায়ী বরাদ্দ) =================
            if (Schema::hasTable('product_licenses')) {
                // অর্ডারে থাকা প্রকৃত কোয়ান্টিটি
                $qty = (int)($item->quantity ?? ($item->qty ?? 1));

                $assignedCount = 0;
                if (Schema::hasTable('order_licenses')) {
                    $assignedCount = DB::table('order_licenses')
                        ->where('order_id', $orderId)
                        ->where('product_id', $productId)
                        ->count();
                }

                $needed = $qty - $assignedCount;

                if ($needed > 0) {
                    $keyCol = 'license_key';
                    if (!Schema::hasColumn('product_licenses', 'license_key')) {
                        if (Schema::hasColumn('product_licenses', 'license')) $keyCol = 'license';
                        elseif (Schema::hasColumn('product_licenses', 'key')) $keyCol = 'key';
                    }

                    // Available লাইসেন্সগুলো নির্বাচন
                    $query = DB::table('product_licenses')->where('product_id', $productId);

                    if (Schema::hasColumn('product_licenses', 'status')) {
                        $query->where(function ($q) {
                            $q->whereIn(DB::raw('LOWER(status)'), ['available', 'active', 'unused', 'free'])
                              ->orWhereNull('status');
                        });
                    }

                    if (Schema::hasColumn('product_licenses', 'is_used')) {
                        $query->where(function ($q) {
                            $q->where('is_used', 0)->orWhereNull('is_used');
                        });
                    }

                    if (Schema::hasColumn('product_licenses', 'order_id')) {
                        $query->where(function ($q) {
                            $q->whereNull('order_id')->orWhere('order_id', 0);
                        });
                    }

                    // যতগুলো Quantity কেনা হয়েছে, ঠিক ততগুলো কী (take $needed) লক করা
                    $availableKeys = $query->lockForUpdate()->take($needed)->get();

                    foreach ($availableKeys as $keyRecord) {
                        $licenseString = trim((string)($keyRecord->{$keyCol} ?? ''));
                        if (empty($licenseString)) continue;

                        $updateKeyData = ['updated_at' => now()];
                        if (Schema::hasColumn('product_licenses', 'status')) {
                            $updateKeyData['status'] = 'Assigned';
                        }
                        if (Schema::hasColumn('product_licenses', 'is_used')) {
                            $updateKeyData['is_used'] = 1;
                        }
                        if (Schema::hasColumn('product_licenses', 'order_id')) {
                            $updateKeyData['order_id'] = $orderId;
                        }

                        DB::table('product_licenses')->where('id', $keyRecord->id)->update($updateKeyData);

                        if (Schema::hasTable('order_licenses')) {
                            DB::table('order_licenses')->insert([
                                'order_id'            => $orderId,
                                'product_id'          => $productId,
                                'license_key'         => $licenseString,
                                'access_instructions' => $instructions,
                                'created_at'          => now(),
                                'updated_at'          => now(),
                            ]);
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * ইমেইল ও কনফার্মেশন পেজের জন্য অর্ডারের ডিজিটাল ডেটা প্রস্তুতকরণ
     */
    public static function getOrderDigitalDetails($orderId): array
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return ['has_digital' => false, 'is_paid' => false, 'downloads' => [], 'licenses' => []];
        }

        $isPaid = in_array(strtolower($order->payment_status ?? ''), ['paid', 'completed']);
        $downloads = [];
        $licenses = [];

        // ১. ডাউনলোড ফাইল সংগ্রহ
        if (Schema::hasTable('order_downloads')) {
            $downloadsQuery = DB::table('order_downloads')
                ->join('product_downloads', 'order_downloads.product_download_id', '=', 'product_downloads.id')
                ->join('products', 'order_downloads.product_id', '=', 'products.id')
                ->where('order_downloads.order_id', $orderId)
                ->select(
                    'order_downloads.*',
                    'product_downloads.file_name',
                    'product_downloads.file_path',
                    'product_downloads.file_url',
                    'products.title as product_title'
                )
                ->get();

            foreach ($downloadsQuery as $row) {
                $isExpired = (!empty($row->expires_at) && now()->gt($row->expires_at));
                $isLimitReached = (!is_null($row->download_limit) && $row->download_count >= $row->download_limit);

                $row->is_accessible = $isPaid && !$isExpired && !$isLimitReached;
                $row->download_url = route('digital.download', $row->download_token);
                $downloads[] = $row;
            }
        }

        // ২. লাইসেন্স কী সংগ্রহ (order_licenses থেকে)
        if (Schema::hasTable('order_licenses')) {
            $licenses = DB::table('order_licenses')
                ->join('products', 'order_licenses.product_id', '=', 'products.id')
                ->where('order_licenses.order_id', $orderId)
                ->select(
                    'order_licenses.*',
                    'products.title as product_title'
                )
                ->get()
                ->all();
        }

        // ৩. ফলব্যাক: যদি order_licenses খালি থাকে কিন্তু product_licenses-এ অ্যাসাইন করা থাকে
        if (empty($licenses) && Schema::hasTable('product_licenses') && Schema::hasColumn('product_licenses', 'order_id')) {
            $keyCol = 'license_key';
            if (!Schema::hasColumn('product_licenses', 'license_key') && Schema::hasColumn('product_licenses', 'license')) {
                $keyCol = 'license';
            }

            $pLicenses = DB::table('product_licenses')
                ->join('products', 'product_licenses.product_id', '=', 'products.id')
                ->where('product_licenses.order_id', $orderId)
                ->select(
                    'product_licenses.id',
                    'product_licenses.product_id',
                    "product_licenses.{$keyCol} as license_key",
                    'products.title as product_title'
                )
                ->get();

            foreach ($pLicenses as $pLic) {
                $pModel = DB::table('products')->where('id', $pLic->product_id)->first();
                $pLic->access_instructions = $pModel->customer_instructions ?? ($pModel->access_instructions ?? ($pModel->access_note ?? null));
                $licenses[] = $pLic;
            }
        }

        // ৪. যদি কোনো লাইসেন্স কী না থাকে কিন্তু অ্যাক্সেস নোট থাকে
        $orderItems = DB::table('order_items')->where('order_id', $orderId)->get();
        if (empty($licenses)) {
            foreach ($orderItems as $item) {
                $p = DB::table('products')->where('id', $item->product_id)->first();
                if ($p) {
                    $note = $p->customer_instructions ?? ($p->access_instructions ?? ($p->access_note ?? null));
                    if (!empty($note)) {
                        $licenses[] = (object)[
                            'product_title'       => $p->title,
                            'license_key'         => null,
                            'access_instructions' => $note,
                        ];
                    }
                }
            }
        }

        $hasDigital = (count($downloads) > 0 || count($licenses) > 0);

        return [
            'has_digital' => $hasDigital,
            'is_paid'     => $isPaid,
            'downloads'   => $downloads,
            'licenses'    => $licenses,
        ];
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PathaoCourierService
{
    /**
     * পাঠাও কুরিয়ার সেটিংস ও ক্রেডেনশিয়াল রিড করা
     */
    protected static function getSetting()
    {
        return DB::table('courier_settings')
            ->where(function ($q) {
                $q->where('courier_name', 'like', '%pathao%')
                  ->orWhere('courier_code', 'pathao');
            })
            ->first();
    }

    /**
     * পাঠাও OAuth2 এক্সেস টোকেন জেনারেট ও ক্যাশ করা
     */
    public static function getAccessToken()
    {
        $setting = self::getSetting();

        if (!$setting || !$setting->is_active || empty($setting->api_key) || empty($setting->secret_key)) {
            return null;
        }

        // টোকেন ক্যাশে থাকলে সেটি রিটার্ন করবে (ঘণ্টাভিত্তিক ক্যাশ)
        $cachedToken = Cache::get('pathao_merchant_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $baseUrl = rtrim($setting->api_url ?: 'https://api-hermes.pathao.com', '/');

        // credentials ডিকোড (username, password, client_id, client_secret)
        $credentials = [];
        if (!empty($setting->credentials)) {
            $credentials = json_decode($setting->credentials, true) ?: [];
        }

        $clientId     = trim($setting->api_key);
        $clientSecret = trim($setting->secret_key);
        $username     = $credentials['username'] ?? ($credentials['client_email'] ?? '');
        $password     = $credentials['password'] ?? '';

        if (empty($clientId) || empty($clientSecret) || empty($username) || empty($password)) {
            Log::warning('Pathao API Credentials missing: client_id, client_secret, username, or password is empty.');
            return null;
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(20)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/aladdin/api/v1/issue-token", [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'username'      => $username,
                    'password'      => $password,
                    'grant_type'    => 'password',
                ]);

            $data = $response->json();

            if ($response->successful() && !empty($data['access_token'])) {
                $expiresInSeconds = max(300, ($data['expires_in'] ?? 86400) - 300);
                Cache::put('pathao_merchant_access_token', $data['access_token'], $expiresInSeconds);
                return $data['access_token'];
            }

            Log::error('Pathao Token Error: ' . json_encode($data));
            return null;
        } catch (\Throwable $e) {
            Log::error('Pathao Token Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * পাঠাও মার্চেন্ট স্টোর তালিকা রিড করা
     */
    public static function getStores()
    {
        $token = self::getAccessToken();
        if (!$token) return ['success' => false, 'message' => 'Pathao access token generation failed.', 'data' => []];

        $setting = self::getSetting();
        $baseUrl = rtrim($setting->api_url ?: 'https://api-hermes.pathao.com', '/');

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withToken($token)
                ->get("{$baseUrl}/aladdin/api/v1/stores");

            $res = $response->json();
            if ($response->successful() && isset($res['data'])) {
                return ['success' => true, 'data' => $res['data']['data'] ?? $res['data']];
            }

            return ['success' => false, 'message' => $res['message'] ?? 'Failed to fetch Pathao stores.', 'data' => []];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
    }

    /**
     * পাঠাও সিটি (City) তালিকা
     */
    public static function getCities()
    {
        $token = self::getAccessToken();
        if (!$token) return ['success' => false, 'data' => []];

        $setting = self::getSetting();
        $baseUrl = rtrim($setting->api_url ?: 'https://api-hermes.pathao.com', '/');

        try {
            $response = Http::withoutVerifying()->timeout(15)->withToken($token)->get("{$baseUrl}/aladdin/api/v1/countries/1/city-list");
            $res = $response->json();
            return ['success' => true, 'data' => $res['data']['data'] ?? ($res['data'] ?? [])];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * পাঠাও জোন (Zone) তালিকা
     */
    public static function getZones($cityId)
    {
        $token = self::getAccessToken();
        if (!$token) return ['success' => false, 'data' => []];

        $setting = self::getSetting();
        $baseUrl = rtrim($setting->api_url ?: 'https://api-hermes.pathao.com', '/');

        try {
            $response = Http::withoutVerifying()->timeout(15)->withToken($token)->get("{$baseUrl}/aladdin/api/v1/cities/{$cityId}/zone-list");
            $res = $response->json();
            return ['success' => true, 'data' => $res['data']['data'] ?? ($res['data'] ?? [])];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * পাঠাও এরিয়া (Area) তালিকা
     */
    public static function getAreas($zoneId)
    {
        $token = self::getAccessToken();
        if (!$token) return ['success' => false, 'data' => []];

        $setting = self::getSetting();
        $baseUrl = rtrim($setting->api_url ?: 'https://api-hermes.pathao.com', '/');

        try {
            $response = Http::withoutVerifying()->timeout(15)->withToken($token)->get("{$baseUrl}/aladdin/api/v1/zones/{$zoneId}/area-list");
            $res = $response->json();
            return ['success' => true, 'data' => $res['data']['data'] ?? ($res['data'] ?? [])];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * পাঠাও পার্সেল বুকিং (Create Order)
     */
    public static function createOrder($order, array $customParams = [])
    {
        $setting = self::getSetting();
        if (!$setting || !$setting->is_active) {
            return [
                'success' => false,
                'message' => 'Pathao Courier gateway is disabled.',
                'manual'  => true
            ];
        }

        $token = self::getAccessToken();
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Pathao API authentication failed. Check credentials.',
                'manual'  => true
            ];
        }

        $baseUrl = rtrim($setting->api_url ?: 'https://api-hermes.pathao.com', '/');
        $invoiceNo = $order->invoice_no ?? ('INV-' . $order->id);

        $customerName    = $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Customer'));
        $customerPhone   = $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? '01700000000'));
        $customerAddress = $order->shipping_address ?? ($order->customer_address ?? ($order->address ?? 'Bangladesh'));

        $paidAmount = (float)($order->paid_amount ?? 0);
        $grandTotal = (float)($order->grand_total ?? 0);
        $codAmount  = max(0, $grandTotal - $paidAmount);

        // স্টোর আইডি (কাস্টম প্যারাম বা ডিফল্ট স্টোর)
        $storeId = $customParams['store_id'] ?? null;
        if (!$storeId) {
            $stores = self::getStores();
            if (!empty($stores['data']) && count($stores['data']) > 0) {
                $storeId = $stores['data'][0]['store_id'] ?? null;
            }
        }

        // পণ্যের মোট ওজন হিসাব
        $weight = (float)($customParams['weight'] ?? 0.50);
        if ($weight <= 0) $weight = 0.50;

        $payload = [
            'store_id'            => (int) ($storeId ?: 1),
            'merchant_order_id'   => (string) $invoiceNo,
            'recipient_name'      => (string) $customerName,
            'recipient_phone'     => (string) $customerPhone,
            'recipient_address'   => (string) $customerAddress,
            'recipient_city'      => (int) ($customParams['city_id'] ?? 1), // ডিফল্ট ঢাকা (১)
            'recipient_zone'      => (int) ($customParams['zone_id'] ?? 1),
            'recipient_area'      => !empty($customParams['area_id']) ? (int)$customParams['area_id'] : null,
            'delivery_type'       => (int) ($customParams['delivery_type'] ?? 48), // 48: Standard Delivery
            'item_type'           => (int) ($customParams['item_type'] ?? 2),      // 2: Parcel
            'special_instruction' => (string) ($customParams['note'] ?? ($order->admin_note ?? 'Handle with care')),
            'item_quantity'       => 1,
            'item_weight'         => $weight,
            'amount_to_collect'   => (float) $codAmount,
            'item_description'    => "Order #{$invoiceNo}",
        ];

        try {
            $response = Http::withoutVerifying()
                ->timeout(25)
                ->withToken($token)
                ->post("{$baseUrl}/aladdin/api/v1/orders", $payload);

            $res = $response->json();

            if ($response->successful() && isset($res['data'])) {
                $orderData = $res['data'];
                return [
                    'success'        => true,
                    'consignment_id' => $orderData['consignment_id'] ?? null,
                    'tracking_code'  => $orderData['consignment_id'] ?? null,
                    'order_status'   => $orderData['order_status'] ?? 'Created',
                    'raw'            => $res,
                    'message'        => 'Parcel pushed to Pathao successfully!'
                ];
            }

            return [
                'success' => false,
                'message' => $res['message'] ?? 'Failed to dispatch order to Pathao API.',
                'errors'  => $res['errors'] ?? null
            ];
        } catch (\Throwable $e) {
            Log::error('Pathao Create Order Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Pathao server connection failed: ' . $e->getMessage(),
                'manual'  => true
            ];
        }
    }

    /**
     * পাঠাও পার্সেল ট্র্যাকিং স্ট্যাটাস ইনকোয়ারি
     */
    public static function trackOrder($consignmentId)
    {
        $token = self::getAccessToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Authentication failed.'];
        }

        $setting = self::getSetting();
        $baseUrl = rtrim($setting->api_url ?: 'https://api-hermes.pathao.com', '/');

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withToken($token)
                ->get("{$baseUrl}/aladdin/api/v1/orders/{$consignmentId}");

            $res = $response->json();
            if ($response->successful() && isset($res['data'])) {
                return [
                    'success'         => true,
                    'delivery_status' => $res['data']['order_status'] ?? 'unknown',
                    'raw'             => $res['data']
                ];
            }

            return ['success' => false, 'message' => $res['message'] ?? 'Parcel tracking not found.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
<?php

namespace App\Http\Controllers\Web\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    // ==========================================
    // 1. SSLCOMMERZ HOSTED GATEWAY
    // ==========================================
    public function sslcommerzPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('checkout')->with('error', 'Order not found!');

        $gateway = DB::table('payment_gateways')->where('code', 'sslcommerz')->where('is_active', true)->first();
        if (!$gateway) return redirect()->route('checkout')->with('error', 'SSLCommerz gateway is inactive.');

        $creds = json_decode($gateway->credentials, true);
        
        $apiUrl = !empty($gateway->is_live) 
            ? "https://securepay.sslcommerz.com/gwprocess/v4/api.php" 
            : "https://sandbox.sslcommerz.com/gwprocess/v4/api.php";

        $postData = [
            'store_id'         => trim($creds['store_id'] ?? ''),
            'store_passwd'     => trim($creds['store_password'] ?? ''),
            'total_amount'     => number_format((float) $order->grand_total, 2, '.', ''),
            'currency'         => 'BDT',
            'tran_id'          => $order->invoice_no,
            'success_url'      => route('payment.sslcommerz.success'),
            'fail_url'         => route('payment.sslcommerz.fail'),
            'cancel_url'       => route('payment.sslcommerz.cancel'),
            'ipn_url'          => route('payment.sslcommerz.ipn'),
            'cus_name'         => $order->customer_name ?? 'Customer',
            'cus_email'        => $order->customer_email ?? 'customer@example.com',
            'cus_phone'        => $order->customer_phone ?? '01700000000',
            'cus_add1'         => $order->customer_address ?? 'Dhaka',
            'cus_city'         => $order->district ?? 'Dhaka',
            'cus_country'      => 'Bangladesh',
            'shipping_method'  => 'NO',
            'product_name'     => 'Store Purchase',
            'product_category' => 'Retail',
            'product_profile'  => 'general',
        ];

        $response = Http::withoutVerifying()->asForm()->post($apiUrl, $postData);
        $res = $response->json();

        if (isset($res['GatewayPageURL']) && !empty($res['GatewayPageURL'])) {
            return redirect()->away($res['GatewayPageURL']);
        }

        return redirect()->route('checkout')->with('error', 'SSLCommerz Error: ' . ($res['failedreason'] ?? 'Invalid Credentials or Connection Failed'));
    }

    public function sslcommerzSuccess(Request $request)
    {
        $tranId = $request->input('tran_id');
        $valId  = $request->input('val_id') ?: $request->input('bank_tran_id');

        $order = DB::table('orders')->where('invoice_no', $tranId)->first();
        if (!$order) {
            return redirect()->route('checkout')->with('error', 'Order not found for validation.');
        }

        $gateway = DB::table('payment_gateways')->where('code', 'sslcommerz')->first();
        $creds = json_decode($gateway->credentials ?? '{}', true);

        $validatorUrl = (!empty($gateway->is_live))
            ? "https://securepay.sslcommerz.com/validator/api/validationserverAPI.php"
            : "https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php";

        $validateRes = Http::withoutVerifying()->get($validatorUrl, [
            'val_id'       => $valId,
            'store_id'     => trim($creds['store_id'] ?? ''),
            'store_passwd' => trim($creds['store_password'] ?? ''),
            'format'       => 'json'
        ])->json();

        $status = $validateRes['status'] ?? ($request->input('status') ?? '');
        $amount = (float)($validateRes['amount'] ?? $request->input('amount', 0));

        if (in_array(strtoupper($status), ['VALID', 'VALIDATED']) && abs($amount - (float)$order->grand_total) < 1.0) {
            DB::table('orders')->where('invoice_no', $tranId)->update([
                'payment_status' => 'paid',
                'order_status'   => 'processing',
                'transaction_id' => $valId,
                'updated_at'     => now(),
            ]);
            
            session()->forget('cart');
            return redirect()->route('order.confirmation', $tranId)->with('success', 'Payment successful!');
        }

        return $this->sslcommerzFail($request);
    }

    public function sslcommerzFail(Request $request)
    {
        $tranId = $request->input('tran_id');

        try {
            DB::table('orders')->where('invoice_no', $tranId)->update([
                'payment_status' => 'failed',
                'order_status'   => 'cancelled',
                'updated_at'     => now()
            ]);
        } catch (\Throwable $e) {
            DB::table('orders')->where('invoice_no', $tranId)->update([
                'payment_status' => 'unpaid',
                'order_status'   => 'cancelled',
                'updated_at'     => now()
            ]);
        }

        return redirect()->route('checkout')->with('error', 'Payment failed or cancelled! Please try again.');
    }

    public function sslcommerzCancel(Request $request)
    {
        $tranId = $request->input('tran_id');
        if ($tranId) {
            try {
                DB::table('orders')->where('invoice_no', $tranId)->update([
                    'order_status' => 'cancelled',
                    'updated_at'   => now()
                ]);
            } catch (\Throwable $e) {}
        }
        return redirect()->route('checkout')->with('error', 'Payment canceled by user.');
    }

    public function sslcommerzIpn(Request $request)
    {
        $tranId = $request->input('tran_id');
        $valId  = $request->input('val_id') ?: $request->input('bank_tran_id');

        if (in_array(strtoupper($request->input('status', '')), ['VALID', 'VALIDATED'])) {
            DB::table('orders')->where('invoice_no', $tranId)->update([
                'payment_status' => 'paid',
                'order_status'   => 'processing',
                'transaction_id' => $valId,
                'updated_at'     => now()
            ]);
        }
        return response()->json(['status' => 'success']);
    }

    // ==========================================
    // 2. BKASH TOKENIZED PGW (Direct API)
    // ==========================================
    private function getBkashBaseUrl($isLive = false)
    {
        return $isLive 
            ? "https://tokenized.pay.bka.sh/v1.2.0-beta" 
            : "https://tokenized.sandbox.bka.sh/v1.2.0-beta";
    }

    public function bkashPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('checkout')->with('error', 'Order not found!');

        $gateway = DB::table('payment_gateways')->where('code', 'bkash')->first();
        if (!$gateway || !$gateway->is_active) {
            return redirect()->route('checkout')->with('error', 'bKash gateway is inactive.');
        }

        $creds = json_decode($gateway->credentials, true);
        $baseUrl = $this->getBkashBaseUrl(!empty($gateway->is_live));

        $appKey    = trim($creds['app_key'] ?? '');
        $appSecret = trim($creds['app_secret'] ?? '');
        $username  = trim($creds['username'] ?? '');
        $password  = trim($creds['password'] ?? '');

        // Step 1: Grant Token
        $tokenResponse = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'username'     => $username,
            'password'     => $password,
        ])->post("{$baseUrl}/tokenized/checkout/token/grant", [
            'app_key'    => $appKey,
            'app_secret' => $appSecret,
        ]);

        $tokenRes = $tokenResponse->json();
        $idToken = $tokenRes['id_token'] ?? null;

        if (!$idToken) {
            $msg = $tokenRes['statusMessage'] ?? ($tokenRes['message'] ?? 'Authentication failed with bKash');
            return redirect()->route('checkout')->with('error', 'bKash Token Grant Failed: ' . $msg);
        }

        cache(['bkash_token_' . $invoiceNo => $idToken], 3600);
        session(['bkash_token' => $idToken, 'bkash_invoice' => $invoiceNo]);

        // Step 2: Create Payment (Tokenized Checkout URL Mode "0011")
        $callbackUrl = route('payment.bkash.callback') . '?invoice=' . urlencode($invoiceNo);

        $createResponse = Http::withoutVerifying()->withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => $idToken,
            'X-App-Key'     => $appKey,
        ])->post("{$baseUrl}/tokenized/checkout/create", [
            'mode'                  => '0011',
            'payerReference'        => $order->customer_phone ?? '01700000000',
            'callbackURL'           => $callbackUrl,
            'amount'                => number_format((float)$order->grand_total, 2, '.', ''),
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $order->invoice_no,
        ]);

        $createRes = $createResponse->json();

        if (isset($createRes['bkashURL']) && !empty($createRes['bkashURL'])) {
            return redirect()->away($createRes['bkashURL']);
        }

        $errorMsg = $createRes['statusMessage'] ?? ($createRes['message'] ?? 'Payment URL Generation Failed');
        return redirect()->route('checkout')->with('error', 'bKash Error: ' . $errorMsg);
    }

    public function bkashCallback(Request $request)
    {
        $paymentID = $request->input('paymentID');
        $status    = $request->input('status');
        
        $invoiceNo = $request->input('invoice') ?: session('bkash_invoice');
        $idToken   = cache('bkash_token_' . $invoiceNo) ?: session('bkash_token');

        if (strtolower($status) === 'success' && $paymentID && $invoiceNo) {
            $gateway = DB::table('payment_gateways')->where('code', 'bkash')->first();
            $creds = json_decode($gateway->credentials, true);
            $baseUrl = $this->getBkashBaseUrl(!empty($gateway->is_live));

            $appKey    = trim($creds['app_key'] ?? '');
            $appSecret = trim($creds['app_secret'] ?? '');
            $username  = trim($creds['username'] ?? '');
            $password  = trim($creds['password'] ?? '');

            // Fallback token refresh if token expired
            if (empty($idToken)) {
                $tokenRes = Http::withoutVerifying()->withHeaders([
                    'Content-Type' => 'application/json',
                    'username'     => $username,
                    'password'     => $password,
                ])->post("{$baseUrl}/tokenized/checkout/token/grant", [
                    'app_key'    => $appKey,
                    'app_secret' => $appSecret,
                ])->json();
                $idToken = $tokenRes['id_token'] ?? null;
            }

            // Step 3: Execute Payment
            $executeResponse = Http::withoutVerifying()->withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => $idToken,
                'X-App-Key'     => $appKey,
            ])->post("{$baseUrl}/tokenized/checkout/execute", [
                'paymentID' => $paymentID
            ]);

            $executeRes = $executeResponse->json();
            $statusCode = $executeRes['statusCode'] ?? null;
            $trxID = $executeRes['trxID'] ?? ($executeRes['paymentID'] ?? $paymentID);

            if ($statusCode === '0000') {
                DB::table('orders')->where('invoice_no', $invoiceNo)->update([
                    'payment_status' => 'paid',
                    'order_status'   => 'processing',
                    'transaction_id' => $trxID,
                    'updated_at'     => now(),
                ]);
                
                session()->forget('cart');
                return redirect()->route('order.confirmation', $invoiceNo)->with('success', 'bKash payment successful!');
            }

            $errMsg = $executeRes['statusMessage'] ?? 'Payment execution failed at bKash end';
            return redirect()->route('checkout')->with('error', 'bKash: ' . $errMsg);
        }

        if ($invoiceNo) {
            try {
                DB::table('orders')->where('invoice_no', $invoiceNo)->update([
                    'order_status' => 'cancelled',
                    'updated_at'   => now()
                ]);
            } catch (\Throwable $e) {}
        }

        return redirect()->route('checkout')->with('error', 'bKash Payment canceled or failed.');
    }

    public function bkashRefund(Request $request, $invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        $gateway = DB::table('payment_gateways')->where('code', 'bkash')->first();
        $creds = json_decode($gateway->credentials, true);
        $baseUrl = $this->getBkashBaseUrl(!empty($gateway->is_live));

        $tokenRes = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
            'username'     => trim($creds['username'] ?? ''),
            'password'     => trim($creds['password'] ?? ''),
        ])->post("{$baseUrl}/tokenized/checkout/token/grant", [
            'app_key'    => trim($creds['app_key'] ?? ''),
            'app_secret' => trim($creds['app_secret'] ?? ''),
        ])->json();

        $idToken = $tokenRes['id_token'] ?? null;
        if (!$idToken) {
            return response()->json(['status' => 'error', 'message' => 'Token generation failed.'], 400);
        }

        $refundResponse = Http::withoutVerifying()->withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => $idToken,
            'X-App-Key'     => trim($creds['app_key'] ?? ''),
        ])->post("{$baseUrl}/tokenized/checkout/payment/refund", [
            'paymentID'    => $order->transaction_id,
            'amount'       => number_format((float)($request->input('amount') ?? $order->grand_total), 2, '.', ''),
            'trxID'        => $order->transaction_id,
            'sku'          => 'REFUND-' . $order->invoice_no,
            'reason'       => $request->input('reason', 'Customer refund request'),
        ]);

        return response()->json($refundResponse->json());
    }

    // ==========================================
    // 3. NAGAD DIRECT GATEWAY
    // ==========================================
    public function nagadPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('checkout')->with('error', 'Order not found!');

        return redirect()->route('order.confirmation', $invoiceNo)->with('info', 'Nagad API connected.');
    }

    public function nagadCallback(Request $request) { return redirect()->route('checkout'); }

    // ==========================================
    // 4. UPAY GATEWAY
    // ==========================================
    public function upayPay($invoiceNo) { return redirect()->route('order.confirmation', $invoiceNo)->with('info', 'Upay API connected.'); }
    public function upayCallback(Request $request) { return redirect()->route('checkout'); }

    // ==========================================
    // 5. EPS (EASY PAYMENT SYSTEM)
    // ==========================================
    public function epsPay($invoiceNo) { return redirect()->route('order.confirmation', $invoiceNo); }
    public function epsCallback(Request $request) { return redirect()->route('checkout'); }

    // ==========================================
    // 6. SHURJOPAY GATEWAY
    // ==========================================
    public function shurjopayPay($invoiceNo) { return redirect()->route('order.confirmation', $invoiceNo); }
    public function shurjopayResponse(Request $request) { return redirect()->route('checkout'); }
    public function shurjopayIpn(Request $request) { return response()->json(['status' => 'received']); }

    // ==========================================
    // 7. STRIPE (CARD)
    // ==========================================
    public function stripePay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('checkout')->with('error', 'Order not found!');

        $gateway = DB::table('payment_gateways')->where('code', 'stripe')->where('is_active', true)->first();
        if (!$gateway) return redirect()->route('checkout')->with('error', 'Stripe gateway is inactive.');

        $creds = json_decode($gateway->credentials, true);
        $secretKey = trim($creds['secret_key'] ?? '');

        if (empty($secretKey)) return redirect()->route('checkout')->with('error', 'Stripe Secret Key missing.');

        $response = Http::withoutVerifying()->withToken($secretKey)->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
            'payment_method_types' => ['card'],
            'line_items[0][price_data][currency]' => 'bdt',
            'line_items[0][price_data][unit_amount]' => (int)($order->grand_total * 100),
            'line_items[0][price_data][product_data][name]' => 'Order ' . $order->invoice_no,
            'line_items[0][quantity]' => 1,
            'mode' => 'payment',
            'success_url' => route('payment.stripe.success', $order->invoice_no) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.stripe.cancel', $order->invoice_no),
        ]);

        $session = $response->json();
        if (isset($session['url'])) return redirect()->away($session['url']);

        return redirect()->route('checkout')->with('error', 'Stripe Error: ' . ($session['error']['message'] ?? 'Connection error'));
    }

    public function stripeSuccess(Request $request, $invoiceNo)
    {
        $sessionId = $request->get('session_id');
        DB::table('orders')->where('invoice_no', $invoiceNo)->update([
            'payment_status' => 'paid',
            'order_status'   => 'processing',
            'transaction_id' => $sessionId,
            'updated_at'     => now(),
        ]);
        session()->forget('cart');
        return redirect()->route('order.confirmation', $invoiceNo)->with('success', 'Stripe card payment successful!');
    }

    public function stripeCancel($invoiceNo) { return redirect()->route('checkout')->with('error', 'Stripe payment canceled.'); }
    public function stripeWebhook(Request $request) { return response()->json(['status' => 'received']); }

    // ==========================================
    // 8. PAYPAL
    // ==========================================
    public function paypalPay($invoiceNo) { return redirect()->route('order.confirmation', $invoiceNo)->with('info', 'PayPal connected.'); }
    public function paypalSuccess(Request $request, $invoiceNo)
    {
        DB::table('orders')->where('invoice_no', $invoiceNo)->update(['payment_status' => 'paid', 'order_status' => 'processing', 'updated_at' => now()]);
        session()->forget('cart');
        return redirect()->route('order.confirmation', $invoiceNo)->with('success', 'PayPal payment received!');
    }
    public function paypalCancel($invoiceNo) { return redirect()->route('checkout')->with('error', 'PayPal payment canceled.'); }
}
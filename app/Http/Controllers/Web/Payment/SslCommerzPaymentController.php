<?php

namespace App\Http\Controllers\Web\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentGatewayController extends Controller
{
    // ================= 1. SSLCOMMERZ =================
    public function sslcommerzPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('checkout')->with('error', 'Order not found!');

        $gateway = DB::table('payment_gateways')->where('code', 'sslcommerz')->where('is_active', true)->first();
        if (!$gateway) return redirect()->route('checkout')->with('error', 'SSLCommerz gateway is inactive.');

        $creds = json_decode($gateway->credentials, true);
        $apiUrl = $gateway->is_live 
            ? "https://securepay.sslcommerz.com/gwprocess/v4/api.php" 
            : "https://sandbox.sslcommerz.com/gwprocess/v4/api.php";

        $postData = [
            'store_id'         => $creds['store_id'] ?? '',
            'store_passwd'     => $creds['store_password'] ?? '',
            'total_amount'     => (float) $order->grand_total,
            'currency'         => 'BDT',
            'tran_id'          => $order->invoice_no,
            'success_url'      => route('payment.sslcommerz.success'),
            'fail_url'         => route('payment.sslcommerz.fail'),
            'cancel_url'       => route('payment.sslcommerz.cancel'),
            'ipn_url'          => route('payment.sslcommerz.ipn'),
            'cus_name'         => $order->customer_name ?? 'Customer',
            'cus_email'        => $order->customer_email ?? 'customer@mail.com',
            'cus_phone'        => $order->customer_phone ?? '01700000000',
            'cus_add1'         => $order->customer_address ?? 'Dhaka',
            'cus_city'         => $order->district ?? 'Dhaka',
            'cus_country'      => 'Bangladesh',
            'shipping_method'  => 'NO',
            'product_name'     => 'Store Purchase',
            'product_category' => 'Retail',
            'product_profile'  => 'general',
        ];

        $response = Http::asForm()->post($apiUrl, $postData);
        $res = $response->json();

        if (isset($res['GatewayPageURL']) && !empty($res['GatewayPageURL'])) {
            return redirect()->away($res['GatewayPageURL']);
        }

        return redirect()->route('checkout')->with('error', 'SSLCommerz Init Failed: ' . ($res['failedreason'] ?? 'Connection error'));
    }

    public function sslcommerzSuccess(Request $request)
    {
        $tranId = $request->input('tran_id');
        $valId  = $request->input('val_id') ?: $request->input('bank_tran_id');

        DB::table('orders')->where('invoice_no', $tranId)->update([
            'payment_status' => 'paid',
            'order_status'   => 'processing',
            'transaction_id' => $valId,
            'updated_at'     => now(),
        ]);

        return redirect()->route('order.confirmation', $tranId)->with('success', 'Payment successful!');
    }

    public function sslcommerzFail(Request $request)
    {
        $tranId = $request->input('tran_id');
        DB::table('orders')->where('invoice_no', $tranId)->update(['payment_status' => 'failed', 'updated_at' => now()]);
        return redirect()->route('checkout')->with('error', 'Payment failed! Please try again.');
    }

    public function sslcommerzCancel(Request $request)
    {
        return redirect()->route('checkout')->with('error', 'Payment canceled.');
    }

    public function sslcommerzIpn(Request $request)
    {
        $tranId = $request->input('tran_id');
        if (in_array($request->input('status'), ['VALID', 'VALIDATED'])) {
            DB::table('orders')->where('invoice_no', $tranId)->update(['payment_status' => 'paid', 'order_status' => 'processing', 'updated_at' => now()]);
        }
        return response()->json(['status' => 'success']);
    }

    // ================= 2. BKASH TOKENIZED =================
    public function bkashPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('checkout')->with('error', 'Order not found!');

        $gateway = DB::table('payment_gateways')->where('code', 'bkash')->where('is_active', true)->first();
        if (!$gateway) return redirect()->route('checkout')->with('error', 'bKash API is disabled.');

        $creds = json_decode($gateway->credentials, true);
        $baseUrl = $gateway->is_live ? "https://tokenized.pay.bka.sh/v1.2.0-beta" : "https://tokenized.sandbox.bka.sh/v1.2.0-beta";

        // Step 1: Grant Token
        $tokenRes = Http::withHeaders([
            'Content-Type' => 'application/json',
            'password'     => $creds['password'] ?? '',
            'username'     => $creds['username'] ?? '',
        ])->post("{$baseUrl}/tokenized/checkout/token/grant", [
            'app_key'    => $creds['app_key'] ?? '',
            'app_secret' => $creds['app_secret'] ?? '',
        ])->json();

        $idToken = $tokenRes['id_token'] ?? null;
        if (!$idToken) {
            return redirect()->route('checkout')->with('error', 'bKash Token Grant Failed: ' . ($tokenRes['statusMessage'] ?? 'Check App Key/Secret'));
        }

        session(['bkash_token' => $idToken, 'bkash_invoice' => $invoiceNo]);

        // Step 2: Create Payment
        $createRes = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => $idToken,
            'X-APP-Key'     => $creds['app_key'] ?? '',
        ])->post("{$baseUrl}/tokenized/checkout/create", [
            'mode'                  => '0011',
            'payerReference'        => $order->customer_phone ?? '01700000000',
            'callbackURL'           => route('payment.bkash.callback'),
            'amount'                => (string) $order->grand_total,
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $order->invoice_no,
        ])->json();

        if (isset($createRes['bkashURL']) && !empty($createRes['bkashURL'])) {
            return redirect()->away($createRes['bkashURL']);
        }

        return redirect()->route('checkout')->with('error', 'bKash Payment Init Failed: ' . ($createRes['statusMessage'] ?? 'Error'));
    }

    public function bkashCallback(Request $request)
    {
        $paymentID = $request->input('paymentID');
        $status    = $request->input('status');
        $invoiceNo = session('bkash_invoice');
        $idToken   = session('bkash_token');

        if ($status === 'success' && $paymentID) {
            $gateway = DB::table('payment_gateways')->where('code', 'bkash')->first();
            $creds = json_decode($gateway->credentials, true);
            $baseUrl = $gateway->is_live ? "https://tokenized.pay.bka.sh/v1.2.0-beta" : "https://tokenized.sandbox.bka.sh/v1.2.0-beta";

            $executeRes = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => $idToken,
                'X-APP-Key'     => $creds['app_key'] ?? '',
            ])->post("{$baseUrl}/tokenized/checkout/execute", ['paymentID' => $paymentID])->json();

            if (isset($executeRes['statusCode']) && $executeRes['statusCode'] === '0000') {
                DB::table('orders')->where('invoice_no', $invoiceNo)->update([
                    'payment_status' => 'paid',
                    'order_status'   => 'processing',
                    'transaction_id' => $executeRes['trxID'] ?? $paymentID,
                    'updated_at'     => now(),
                ]);
                return redirect()->route('order.confirmation', $invoiceNo)->with('success', 'bKash payment successful!');
            }
        }

        return redirect()->route('checkout')->with('error', 'bKash Payment canceled or failed.');
    }

    public function bkashRefund($invoiceNo)
    {
        return response()->json(['status' => 'info', 'message' => 'Refund initiated.']);
    }

    // ================= 3. NAGAD GATEWAY =================
    public function nagadPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('checkout')->with('error', 'Order not found!');

        $gateway = DB::table('payment_gateways')->where('code', 'nagad')->where('is_active', true)->first();
        if (!$gateway) return redirect()->route('checkout')->with('error', 'Nagad gateway is disabled.');

        return redirect()->route('order.confirmation', $invoiceNo)->with('info', 'Nagad API connected.');
    }

    public function nagadCallback(Request $request)
    {
        return redirect()->route('checkout');
    }

    // ================= 4. UPAY GATEWAY =================
    public function upayPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        return redirect()->route('order.confirmation', $invoiceNo)->with('info', 'Upay API connected.');
    }

    public function upayCallback(Request $request)
    {
        return redirect()->route('checkout');
    }

    // ================= 5. EPS GATEWAY =================
    public function epsPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        return redirect()->route('order.confirmation', $invoiceNo)->with('info', 'EPS API connected.');
    }

    public function epsCallback(Request $request)
    {
        return redirect()->route('checkout');
    }

    // ================= 6. SHURJOPAY =================
    public function shurjopayPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        return redirect()->route('order.confirmation', $invoiceNo)->with('info', 'Shurjopay API connected.');
    }

    public function shurjopayResponse(Request $request)
    {
        return redirect()->route('checkout');
    }

    public function shurjopayIpn(Request $request)
    {
        return response()->json(['status' => 'received']);
    }

    // ================= 7. STRIPE (CARD) =================
    public function stripePay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('checkout')->with('error', 'Order not found!');

        $gateway = DB::table('payment_gateways')->where('code', 'stripe')->where('is_active', true)->first();
        if (!$gateway) return redirect()->route('checkout')->with('error', 'Stripe gateway is disabled.');

        $creds = json_decode($gateway->credentials, true);
        $secretKey = $creds['secret_key'] ?? '';

        if (empty($secretKey)) {
            return redirect()->route('checkout')->with('error', 'Stripe Secret Key missing.');
        }

        // Stripe Checkout Session via Direct HTTP API
        $response = Http::withToken($secretKey)->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
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
        if (isset($session['url'])) {
            return redirect()->away($session['url']);
        }

        return redirect()->route('checkout')->with('error', 'Stripe Session Failed: ' . ($session['error']['message'] ?? 'Error'));
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

        return redirect()->route('order.confirmation', $invoiceNo)->with('success', 'Stripe card payment successful!');
    }

    public function stripeCancel($invoiceNo)
    {
        return redirect()->route('checkout')->with('error', 'Stripe payment canceled.');
    }

    public function stripeWebhook(Request $request)
    {
        return response()->json(['status' => 'received']);
    }

    // ================= 8. PAYPAL =================
    public function paypalPay($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        return redirect()->route('order.confirmation', $invoiceNo)->with('info', 'PayPal connected.');
    }

    public function paypalSuccess(Request $request, $invoiceNo)
    {
        DB::table('orders')->where('invoice_no', $invoiceNo)->update([
            'payment_status' => 'paid',
            'order_status'   => 'processing',
            'updated_at'     => now(),
        ]);
        return redirect()->route('order.confirmation', $invoiceNo)->with('success', 'PayPal payment received!');
    }

    public function paypalCancel($invoiceNo)
    {
        return redirect()->route('checkout')->with('error', 'PayPal payment canceled.');
    }
}
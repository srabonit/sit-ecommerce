<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\EmailAbandonedCart;
use App\Models\EmailSubscriber;

class AbandonedCartController extends Controller
{
    /**
     * Real-time checkout AJAX sync hook
     * চেকআউটে গ্রাহক নাম, ইমেইল বা ফোন টাইপ করলেই ব্যাকগ্রাউন্ডে কার্ট স্ন্যাপশট ডাটাবেজে স্টোর হবে।
     */
    public function sync(Request $request)
    {
        $rawEmail = strtolower(trim($request->input('email', '')));
        $phone    = trim($request->input('phone', ''));
        $name     = trim($request->input('name', ''));

        // ফোন নম্বর থেকে অপ্রয়োজনীয় ক্যারেক্টার ক্লিন করা
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // ইমেইলটি প্রকৃত ভ্যালিড ইমেইল কিনা যাচাই
        $isValidEmail = filter_var($rawEmail, FILTER_VALIDATE_EMAIL);
        $email = $isValidEmail ? $rawEmail : null;

        // ইমেইল বা ভ্যালিড ফোন দুটোর অন্তত একটি না থাকলে সেভ করার প্রয়োজন নেই
        if (!$email && strlen($cleanPhone) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'No valid contact information provided.'
            ]);
        }

        // একটিভ সেশন থেকে কার্ট আইটেমস রিড করা
        $cart = Session::get('cart', []);
        if (empty($cart) || !is_array($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Active cart is empty.'
            ]);
        }

        $cartValue = 0.0;
        $itemsSnapshot = [];

        foreach ($cart as $key => $item) {
            $qty   = (int)($item['quantity'] ?? ($item['qty'] ?? 1));
            $rawPrice = $item['price'] ?? ($item['unit_price'] ?? 0);
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $price = (float)$rawPrice;
            $subTotal = $qty * $price;
            $cartValue += $subTotal;

            $itemsSnapshot[] = [
                'product_id' => $item['id'] ?? ($item['product_id'] ?? null),
                'name'       => $item['name'] ?? ($item['title'] ?? 'Product'),
                'price'      => $price,
                'quantity'   => $qty,
                'image'      => $item['image'] ?? ($item['thumbnail'] ?? null),
                'color'      => $item['color'] ?? null,
                'size'       => $item['size'] ?? null,
                'subtotal'   => $subTotal,
            ];
        }

        if ($cartValue <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart value is zero.'
            ]);
        }

        // ব্রাউজার সেশনে সক্রিয় রিকভারি টোকেন ট্র্যাক রাখা (ডুপ্লিকেট রো প্রতিরোধে)
        $sessionRecoveryToken = Session::get('active_abandoned_cart_token');

        // ১. স্মার্ট রিকনসিলিয়েশন কুয়েরি: সেশন টোকেন, ভ্যালিড ফোন অথবা ভ্যালিড ইমেইল দিয়ে ম্যাচ করা
        $abandoned = EmailAbandonedCart::where('is_recovered', false)
            ->where(function ($q) use ($sessionRecoveryToken, $phone, $cleanPhone, $email) {
                if ($sessionRecoveryToken) {
                    $q->orWhere('recovery_token', $sessionRecoveryToken);
                }
                if ($email) {
                    $q->orWhere('email', $email);
                }
                if (!empty($phone)) {
                    $q->orWhere('phone', $phone);
                    if ($cleanPhone !== $phone) {
                        $q->orWhere('phone', $cleanPhone);
                    }
                    $q->orWhere('email', $cleanPhone . '@guest.shopnowbd.online');
                    $q->orWhere('email', $phone . '@guest.shopnowbd.online');
                }
                if (Auth::check()) {
                    $q->orWhere('user_id', Auth::id());
                }
            })
            ->latest('id')
            ->first();

        $resolvedEmail = $email ?: ($abandoned && !str_contains($abandoned->email, '@guest.') ? $abandoned->email : ($cleanPhone ? $cleanPhone . '@guest.shopnowbd.online' : null));
        $resolvedPhone = !empty($phone) ? $phone : ($abandoned->phone ?? null);
        $resolvedName  = !empty($name) ? $name : ($abandoned->customer_name ?? 'Valued Customer');

        if ($abandoned) {
            // পূর্বের পেন্ডিং কার্টটিকেই নতুন ডাটা দিয়ে আপডেট করা
            $abandoned->update([
                'user_id'          => Auth::id() ?? $abandoned->user_id,
                'email'            => $resolvedEmail ?: $abandoned->email,
                'phone'            => $resolvedPhone,
                'customer_name'    => $resolvedName,
                'cart_items'       => $itemsSnapshot,
                'cart_value'       => $cartValue,
                'abandoned_at'     => now(),
            ]);

            // একই গ্রাহকের পূর্বের কোনো ঝুলন্ত ডুপ্লিকেট পেন্ডিং রো থাকলে স্বয়ংক্রিয়ভাবে মুছে ফেলা
            if ($resolvedPhone || $email) {
                EmailAbandonedCart::where('is_recovered', false)
                    ->where('id', '!=', $abandoned->id)
                    ->where(function ($q) use ($resolvedPhone, $email) {
                        if ($resolvedPhone) {
                            $q->orWhere('phone', $resolvedPhone);
                        }
                        if ($email) {
                            $q->orWhere('email', $email);
                        }
                    })
                    ->delete();
            }
        } else {
            // নতুন রেকর্ড তৈরি করা
            $newToken = Str::random(64);
            $abandoned = EmailAbandonedCart::create([
                'user_id'             => Auth::id() ?? null,
                'email'               => $resolvedEmail ?: ($cleanPhone . '@guest.shopnowbd.online'),
                'phone'               => $resolvedPhone,
                'customer_name'       => $resolvedName,
                'cart_items'          => $itemsSnapshot,
                'cart_value'          => $cartValue,
                'recovery_token'      => $newToken,
                'reminder_sent_count' => 0,
                'is_recovered'        => false,
                'abandoned_at'        => now(),
            ]);

            Session::put('active_abandoned_cart_token', $abandoned->recovery_token);
        }

        // সেশনে সর্বদা লেটেস্ট রিকভারি টোকেন সংরক্ষণ
        Session::put('active_abandoned_cart_token', $abandoned->recovery_token);

        // গ্রাহককে স্বয়ংক্রিয়ভাবে মার্কেটিং সাবস্ক্রাইবার তালিকায় যুক্ত করা
        if ($email) {
            EmailSubscriber::firstOrCreate(
                ['email' => $email],
                [
                    'first_name'        => $resolvedName !== 'Valued Customer' ? $resolvedName : null,
                    'phone'             => $resolvedPhone,
                    'status'            => 'subscribed',
                    'marketing_consent' => true,
                    'consent_source'    => 'checkout_abandoned_sync',
                    'consent_at'        => now(),
                ]
            );
        }

        return response()->json([
            'success'        => true,
            'cart_id'        => $abandoned->id,
            'recovery_token' => $abandoned->recovery_token,
        ]);
    }
}
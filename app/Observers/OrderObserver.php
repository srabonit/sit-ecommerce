<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\EmailAbandonedCart;
use App\Models\EmailSubscriber;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * যখনই গ্রাহক কোনো অর্ডার সম্পন্ন করবেন, সাথে সাথে পেন্ডিং থাকা পরিত্যক্ত কার্ট রিকভার হিসেবে চিহ্নিত হবে।
     */
    public function created(Order $order): void
    {
        $email = $order->customer_email ?? ($order->email ?? ($order->shipping_email ?? null));
        $phone = $order->customer_phone ?? ($order->phone ?? ($order->shipping_phone ?? null));

        if (empty($email) && empty($phone)) {
            return;
        }

        // গ্রাহকের পেন্ডিং থাকা সর্বশেষ পরিত্যক্ত কার্টটি খুঁজে বের করা
        $query = EmailAbandonedCart::where('is_recovered', false);

        if (!empty($email)) {
            $query->where('email', strtolower(trim($email)));
        } else {
            $query->where('phone', trim($phone));
        }

        $abandonedCart = $query->latest()->first();

        // রিকভার স্ট্যাটাস এবং অর্ডারের আইডি লিংক করা
        if ($abandonedCart) {
            $abandonedCart->update([
                'is_recovered'       => true,
                'recovered_order_id' => $order->id,
                'updated_at'         => now(),
            ]);
        }

        // সফলভাবে অর্ডার করা কাস্টমারকে মার্কেটিং অডিয়েন্স সাবস্ক্রাইবার তালিকায় সংযুক্ত করা
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            EmailSubscriber::firstOrCreate(
                ['email' => strtolower(trim($email))],
                [
                    'first_name'        => $order->customer_name ?? ($order->shipping_name ?? ($order->name ?? 'Customer')),
                    'phone'             => $phone,
                    'status'            => 'subscribed',
                    'marketing_consent' => true,
                    'consent_source'    => 'checkout_order_placed',
                    'consent_at'        => now(),
                ]
            );
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FakeOrderSeeder extends Seeder
{
    public function run()
    {
        // 1. Existing Products fetch kora
        $products = DB::table('products')->get();
        if ($products->isEmpty()) {
            $this->command->error('Kono product pawa jayni! Age kichu product create korun ba product seed korun.');
            return;
        }

        // 2. Database order_type column-er allowed ENUM values dynamically detect kora
        $allowedOrderTypes = [];
        $hasOrderType = Schema::hasColumn('orders', 'order_type');
        if ($hasOrderType) {
            try {
                $colInfo = DB::select("SHOW COLUMNS FROM orders WHERE Field = 'order_type'");
                if (!empty($colInfo[0]->Type) && str_starts_with($colInfo[0]->Type, 'enum')) {
                    preg_match_all("/'([^']+)'/", $colInfo[0]->Type, $matches);
                    $allowedOrderTypes = $matches[1] ?? [];
                }
            } catch (\Throwable $e) {
                $allowedOrderTypes = [];
            }
        }

        // 3. Fake Customer Names & Phones pool
        $customerPool = [
            ['name' => 'Tanvir Ahmed', 'phone' => '01711223344', 'address' => 'Mirpur-10, Dhaka'],
            ['name' => 'MD Mizanur Rahman', 'phone' => '01815427791', 'address' => 'Ramgati, Karunanagar, Lakshmipur'],
            ['name' => 'Srabon Chowdhury', 'phone' => '01912345678', 'address' => 'Uttara Sector 7, Dhaka'],
            ['name' => 'Sadia Islam', 'phone' => '01678901234', 'address' => 'Dhanmondi 27, Dhaka'],
            ['name' => 'Kamrul Hasan', 'phone' => '01555667788', 'address' => 'Agrabad, Chattogram'],
            ['name' => 'Nusrat Jahan', 'phone' => '01799887766', 'address' => 'Chawkbazar, Sylhet'],
            ['name' => 'Arif Hossain', 'phone' => '01822334455', 'address' => 'Zindabazar, Sylhet'],
            ['name' => 'Fahim Faisal', 'phone' => '01311224455', 'address' => 'Rajshahi Sadar, Rajshahi'],
            ['name' => 'Rashedul Karim', 'phone' => '01988776655', 'address' => 'Khulna City, Khulna'],
            ['name' => 'Walk-in Customer', 'phone' => 'N/A', 'address' => 'Counter Store'],
        ];

        $paymentMethods = ['cash', 'bkash', 'nagad', 'cod', 'card'];
        $couriers = ['Steadfast Courier', 'Pathao Courier', 'RedX', 'Sundarban Courier'];

        $totalFakeOrders = 250; // 250-ti fake order seed hobe

        for ($i = 1; $i <= $totalFakeOrders; $i++) {
            // 4. Random Date in Last 12 Months
            $randomDaysAgo = rand(0, 365);
            $randomMinutesAgo = rand(0, 1440);
            $createdAt = Carbon::now()->subDays($randomDaysAgo)->subMinutes($randomMinutesAgo);

            // 5. Source (70% Web, 30% POS)
            $isPos = (rand(1, 100) <= 30);
            $customer = $isPos ? $customerPool[9] : $customerPool[rand(0, 8)];

            // 6. Safe order_type resolution (Data truncated error avoid korar jonno)
            $safeOrderTypeVal = null;
            if ($hasOrderType) {
                if (!empty($allowedOrderTypes)) {
                    if ($isPos) {
                        $safeOrderTypeVal = in_array('pos', $allowedOrderTypes) ? 'pos' : ($allowedOrderTypes[0] ?? 'pos');
                    } else {
                        if (in_array('online', $allowedOrderTypes)) {
                            $safeOrderTypeVal = 'online';
                        } elseif (in_array('web', $allowedOrderTypes)) {
                            $safeOrderTypeVal = 'web';
                        } elseif (in_array('regular', $allowedOrderTypes)) {
                            $safeOrderTypeVal = 'regular';
                        } elseif (in_array('physical', $allowedOrderTypes)) {
                            $safeOrderTypeVal = 'physical';
                        } else {
                            $nonPos = array_values(array_filter($allowedOrderTypes, fn($v) => $v !== 'pos'));
                            $safeOrderTypeVal = !empty($nonPos) ? $nonPos[0] : null;
                        }
                    }
                } else {
                    $safeOrderTypeVal = $isPos ? 'pos' : 'online';
                }
            }

            // 7. Status & Payment status logic
            if ($isPos) {
                $orderStatus = 'delivered';
                $isPaid = (rand(1, 100) <= 90);
            } else {
                $statusRand = rand(1, 100);
                if ($statusRand <= 65) {
                    $orderStatus = 'delivered';
                    $isPaid = (rand(1, 100) <= 85);
                } elseif ($statusRand <= 80) {
                    $orderStatus = 'confirmed';
                    $isPaid = (rand(1, 100) <= 50);
                } elseif ($statusRand <= 90) {
                    $orderStatus = 'pending';
                    $isPaid = (rand(1, 100) <= 30);
                } elseif ($statusRand <= 95) {
                    $orderStatus = 'cancelled';
                    $isPaid = false;
                } else {
                    $orderStatus = 'returned';
                    $isPaid = false;
                }
            }

            $paymentStatus = $isPaid ? 'paid' : 'unpaid';
            $paymentMethod = $isPos ? (rand(1, 100) <= 70 ? 'cash' : 'bkash') : $paymentMethods[array_rand($paymentMethods)];

            // 8. Selected Products calculation (1 to 3 items per order)
            $itemCount = rand(1, 3);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            $subtotal = 0;
            $orderItemsData = [];

            foreach ($selectedProducts as $prod) {
                $qty = rand(1, 2);
                $price = (float) (!empty($prod->sale_price) && $prod->sale_price > 0 ? $prod->sale_price : $prod->regular_price);
                if ($price <= 0) $price = rand(200, 1500);

                $itemTotal = $price * $qty;
                $subtotal += $itemTotal;

                $orderItemsData[] = [
                    'product_id' => $prod->id,
                    'title'      => $prod->title,
                    'price'      => $price,
                    'quantity'   => $qty,
                    'total'      => $itemTotal,
                ];
            }

            $shippingCharge = $isPos ? 0 : (rand(1, 100) <= 60 ? 60 : 120);
            $discount = (rand(1, 100) <= 20) ? rand(20, 100) : 0;
            $grandTotal = max(0, ($subtotal + $shippingCharge) - $discount);

            $paidAmount = $isPaid ? $grandTotal : 0;
            $dueAmount  = $isPaid ? 0 : $grandTotal;

            $invoiceNo = ($isPos ? 'POS-' : 'INV-') . $createdAt->format('Ymd') . '-' . strtoupper(Str::random(4));

            // 9. Order Insert Data prepare
            $orderInsert = [
                'order_status'   => $orderStatus,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'grand_total'    => $grandTotal,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ];

            if (Schema::hasColumn('orders', 'invoice_no')) {
                $orderInsert['invoice_no'] = $invoiceNo;
            }
            if (Schema::hasColumn('orders', 'order_number')) {
                $orderInsert['order_number'] = $invoiceNo;
            }
            if (Schema::hasColumn('orders', 'order_source')) {
                $orderInsert['order_source'] = $isPos ? 'pos' : 'web';
            }
            if ($hasOrderType && $safeOrderTypeVal !== null) {
                $orderInsert['order_type'] = $safeOrderTypeVal;
            }
            if (Schema::hasColumn('orders', 'paid_amount')) {
                $orderInsert['paid_amount'] = $paidAmount;
            }
            if (Schema::hasColumn('orders', 'due_amount')) {
                $orderInsert['due_amount'] = $dueAmount;
            }
            if (Schema::hasColumn('orders', 'subtotal')) {
                $orderInsert['subtotal'] = $subtotal;
            }
            if (Schema::hasColumn('orders', 'shipping_charge')) {
                $orderInsert['shipping_charge'] = $shippingCharge;
            }
            if (Schema::hasColumn('orders', 'discount_amount')) {
                $orderInsert['discount_amount'] = $discount;
            }

            // Customer columns safe assignment
            if (Schema::hasColumn('orders', 'customer_name')) $orderInsert['customer_name'] = $customer['name'];
            if (Schema::hasColumn('orders', 'shipping_name')) $orderInsert['shipping_name'] = $customer['name'];
            if (Schema::hasColumn('orders', 'customer_phone')) $orderInsert['customer_phone'] = $customer['phone'];
            if (Schema::hasColumn('orders', 'shipping_phone')) $orderInsert['shipping_phone'] = $customer['phone'];
            if (Schema::hasColumn('orders', 'customer_address')) $orderInsert['customer_address'] = $customer['address'];
            if (Schema::hasColumn('orders', 'shipping_address')) $orderInsert['shipping_address'] = $customer['address'];

            // Courier assignment
            if (!$isPos && in_array($orderStatus, ['shipped', 'delivered'])) {
                if (Schema::hasColumn('orders', 'courier_name')) {
                    $orderInsert['courier_name'] = $couriers[array_rand($couriers)];
                }
                if (Schema::hasColumn('orders', 'courier_tracking_id')) {
                    $orderInsert['courier_tracking_id'] = 'TRK' . rand(1000000, 9999999);
                }
            }

            $orderId = DB::table('orders')->insertGetId($orderInsert);

            // 10. Order Items Insert
            foreach ($orderItemsData as $item) {
                $itemRow = [
                    'order_id'   => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if (Schema::hasColumn('order_items', 'product_name')) $itemRow['product_name'] = $item['title'];
                if (Schema::hasColumn('order_items', 'unit_price')) $itemRow['unit_price'] = $item['price'];
                if (Schema::hasColumn('order_items', 'price')) $itemRow['price'] = $item['price'];
                if (Schema::hasColumn('order_items', 'subtotal')) $itemRow['subtotal'] = $item['total'];
                if (Schema::hasColumn('order_items', 'total_price')) $itemRow['total_price'] = $item['total'];

                DB::table('order_items')->insert($itemRow);
            }
        }

        $this->command->info("Success: {$totalFakeOrders} ti fake orders last 12 months-er random date shoho successfully create hoyeche!");
    }
}
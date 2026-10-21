<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\PaymentVerificationHistory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaymentTransactionService
{
    protected static bool $schemaChecked = false;

    /**
     * Auto-ensure `deleted_at` column exists on `payment_transactions` table for Trash system
     */
    public static function ensureTrashColumnExists(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;

        try {
            if (Schema::hasTable('payment_transactions') && !Schema::hasColumn('payment_transactions', 'deleted_at')) {
                Schema::table('payment_transactions', function (Blueprint $table) {
                    $table->timestamp('deleted_at')->nullable()->index();
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Could not auto-add deleted_at to payment_transactions: ' . $e->getMessage());
        }
    }

    /**
     * ১. Transaction ID Normalize করা (স্পেস, ড্যাশ ও সিম্বল বাদ দিয়ে বড় হাতের অক্ষরে রূপান্তর)
     */
    public static function normalizeTrxId(?string $trxId): ?string
    {
        return PaymentTransaction::normalizeTrxId($trxId);
    }

    /**
     * ২. কঠোর ডুপ্লিকেট Transaction ID যাচাই (Active ও Non-Trashed রেকর্ডের ওপর)
     *
     * @param string|null $trxId
     * @param string|null $paymentMethod
     * @param int|null $excludeTransactionId
     * @param int|null $excludeOrderId
     * @return array ['is_duplicate' => bool, 'message' => string|null, 'existing_transaction' => object|null, 'existing_order' => object|null]
     */
    public static function checkDuplicateTransactionId(
        ?string $trxId,
        ?string $paymentMethod = null,
        ?int $excludeTransactionId = null,
        ?int $excludeOrderId = null
    ): array {
        self::ensureTrashColumnExists();
        $normalized = self::normalizeTrxId($trxId);

        if (empty($normalized)) {
            return [
                'is_duplicate'         => false,
                'normalized_trx_id'    => null,
                'message'              => null,
                'existing_transaction' => null,
                'existing_order'       => null,
            ];
        }

        // ধাপ ক: payment_transactions টেবিলে চেক করা (শুধুমাত্র যেগুলো Trash-এ নেই)
        if (Schema::hasTable('payment_transactions')) {
            $query = DB::table('payment_transactions')
                ->whereNull('deleted_at')
                ->where(function ($q) use ($normalized, $trxId) {
                    $q->where('normalized_trx_id', $normalized)
                      ->orWhereRaw('UPPER(TRIM(transaction_id)) = ?', [strtoupper(trim((string) $trxId))]);
                });

            if (!empty($excludeTransactionId)) {
                $query->where('id', '!=', $excludeTransactionId);
            }

            // একই অর্ডারের আগের ট্রানজেকশন যেহেতু নতুন এন্ট্রির সময় Trash-এ চলে যাবে, তাই একই order_id এক্সক্লুড করা যাবে
            if (!empty($excludeOrderId)) {
                $query->where(function ($q) use ($excludeOrderId) {
                    $q->whereNull('order_id')->orWhere('order_id', '!=', $excludeOrderId);
                });
            }

            $existingTxn = $query->first();

            if ($existingTxn) {
                $orderRef = $existingTxn->invoice_no ?: ('#' . ($existingTxn->order_id ?? $existingTxn->id));
                $statusReadable = ucwords(str_replace('_', ' ', (string) $existingTxn->verification_status));

                return [
                    'is_duplicate'         => true,
                    'normalized_trx_id'    => $normalized,
                    'message'              => 'এই Transaction ID আগে ব্যবহার করা হয়েছে। অনুগ্রহ করে সঠিক Transaction ID যাচাই করে আবার চেষ্টা করুন।',
                    'admin_warning'        => "Duplicate TrxID [{$trxId}] previously used in Order {$orderRef} (Status: {$statusReadable}, Method: {$existingTxn->payment_method}).",
                    'existing_transaction' => $existingTxn,
                    'existing_order'       => $existingTxn->order_id ? DB::table('orders')->where('id', $existingTxn->order_id)->first() : null,
                ];
            }
        }

        // ধাপ খ: লিগ্যাসি orders টেবিলেও সরাসরি চেক করা
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'transaction_id')) {
            $ordQuery = DB::table('orders')
                ->whereNotNull('transaction_id')
                ->where('transaction_id', '!=', '')
                ->where(function ($q) use ($normalized, $trxId) {
                    $q->whereRaw("UPPER(REGEXP_REPLACE(transaction_id, '[^A-Za-z0-9]', '')) = ?", [$normalized])
                      ->orWhereRaw('UPPER(TRIM(transaction_id)) = ?', [strtoupper(trim((string) $trxId))]);
                });

            if (!empty($excludeOrderId)) {
                $ordQuery->where('id', '!=', $excludeOrderId);
            }

            try {
                $existingOrder = $ordQuery->first();
            } catch (\Throwable $e) {
                // MySQL 5.7 fallback (যদি REGEXP_REPLACE সাপোর্ট না করে)
                $existingOrder = DB::table('orders')
                    ->whereNotNull('transaction_id')
                    ->where('transaction_id', '!=', '')
                    ->when($excludeOrderId, fn($q) => $q->where('id', '!=', $excludeOrderId))
                    ->whereRaw('UPPER(REPLACE(REPLACE(TRIM(transaction_id), " ", ""), "-", "")) = ?', [$normalized])
                    ->first();
            }

            if ($existingOrder) {
                $orderRef = $existingOrder->invoice_no ?? ('#' . $existingOrder->id);
                return [
                    'is_duplicate'         => true,
                    'normalized_trx_id'    => $normalized,
                    'message'              => 'এই Transaction ID আগে ব্যবহার করা হয়েছে। অনুগ্রহ করে সঠিক Transaction ID যাচাই করে আবার চেষ্টা করুন।',
                    'admin_warning'        => "Duplicate TrxID [{$trxId}] found in existing Order {$orderRef} (Payment Status: " . strtoupper($existingOrder->payment_status ?? 'unpaid') . ").",
                    'existing_transaction' => null,
                    'existing_order'       => $existingOrder,
                ];
            }
        }

        return [
            'is_duplicate'         => false,
            'normalized_trx_id'    => $normalized,
            'message'              => null,
            'existing_transaction' => null,
            'existing_order'       => null,
        ];
    }

    /**
     * হেল্পার: একই অর্ডারের আগের সকল Active ট্রানজেকশনকে স্বয়ংক্রিয়ভাবে Trash-এ পাঠানো
     * (যাতে ১টি অর্ডারের জন্য ১টি মাত্র Active ট্রানজেকশন থাকে এবং Revenue/Paid ডুপ্লিকেট না হয়)
     */
    public static function trashPreviousOrderTransactions(int $orderId, string $reason = 'Replaced by newer transaction entry'): int
    {
        self::ensureTrashColumnExists();

        $previousTxns = PaymentTransaction::where('order_id', $orderId)
            ->whereNull('deleted_at')
            ->get();

        $trashedCount = 0;
        foreach ($previousTxns as $oldTxn) {
            $oldSt = $oldTxn->verification_status;
            $oldTxn->update([
                'deleted_at'    => now(),
                'updated_by'    => Auth::id(),
                'admin_remarks' => ($oldTxn->admin_remarks ? $oldTxn->admin_remarks . ' | ' : '') . '[Moved to Trash: ' . $reason . ']',
            ]);

            self::recordHistory(
                $oldTxn,
                'moved_to_trash',
                $oldSt,
                'trashed',
                (float) $oldTxn->verified_amount,
                $reason,
                'Previous transaction moved to trash automatically to prevent duplicate order calculation.'
            );
            $trashedCount++;
        }

        return $trashedCount;
    }

    /**
     * ৩. কাস্টমার চেকআউট থেকে আসা পেমেন্ট রিকোয়েস্ট সংরক্ষণ
     */
    public static function recordCheckoutTransaction(object $order, array $payload = []): ?PaymentTransaction
    {
        self::ensureTrashColumnExists();
        if (!Schema::hasTable('payment_transactions')) {
            return null;
        }

        // একই অর্ডারের পুরোনো ট্রানজেকশন থাকলে তা Trash-এ পাঠানো
        self::trashPreviousOrderTransactions((int) $order->id, 'New checkout payment submission');

        $payMethod = trim((string) ($payload['payment_method'] ?? ($order->payment_method ?? 'Cash on Delivery')));
        $payMethodLower = strtolower($payMethod);
        $payType = strtolower((string) ($payload['payment_type'] ?? ''));

        // Source Type নির্ধারণ
        if (in_array($payMethodLower, ['cod', 'cash on delivery']) || $payType === 'cod') {
            $sourceType = PaymentTransaction::SOURCE_COD;
        } elseif ($payType === 'gateway') {
            $sourceType = PaymentTransaction::SOURCE_GATEWAY;
        } else {
            $sourceType = PaymentTransaction::SOURCE_CHECKOUT;
        }

        $grandTotal = (float) ($order->grand_total ?? 0);
        $delivFee   = (float) ($order->delivery_charge ?? ($order->shipping_charge ?? 0));
        $rawTrx     = !empty($payload['transaction_id']) ? trim((string) $payload['transaction_id']) : ($order->transaction_id ?? null);
        $normTrx    = self::normalizeTrxId($rawTrx);
        $senderNo   = !empty($payload['sender_number']) ? trim((string) $payload['sender_number']) : ($order->sender_number ?? null);
        $screenshot = $payload['screenshot_path'] ?? ($order->payment_screenshot ?? null);

        // গুরুত্বপূর্ণ নিয়ম: Checkout থেকে রিকোয়েস্ট জমা দেওয়া মানেই Paid নয় (যদি না বিল ০ টাকা হয়)
        $isZeroBill = ($grandTotal <= 0);
        $initialStatus = $isZeroBill
            ? PaymentTransaction::STATUS_PAID
            : PaymentTransaction::STATUS_PENDING_VERIFICATION;

        $submittedAmt = ($sourceType === PaymentTransaction::SOURCE_COD)
            ? 0.00
            : (float) ($payload['submitted_amount'] ?? $grandTotal);

        $txn = PaymentTransaction::create([
            'transaction_uid'       => PaymentTransaction::generateUniqueUid(),
            'order_id'              => $order->id,
            'order_number'          => $order->order_number ?? (string) $order->id,
            'invoice_no'            => $order->invoice_no ?? ('ORD-' . $order->id),
            'branch_id'             => $order->branch_id ?? null,
            'user_id'               => $order->user_id ?? null,
            'customer_name'         => $order->customer_name ?? ($order->shipping_name ?? ($order->name ?? 'Guest Customer')),
            'customer_phone'        => $order->customer_phone ?? ($order->shipping_phone ?? ($order->phone ?? null)),
            'customer_email'        => $order->customer_email ?? ($order->shipping_email ?? ($order->email ?? null)),
            'source_type'           => $sourceType,
            'payment_method'        => $payMethod,
            'payment_provider'      => $payMethod,
            'sender_number'         => $senderNo,
            'transaction_id'        => $rawTrx,
            'normalized_trx_id'     => $normTrx,
            'is_duplicate_flagged'  => false,
            'expected_amount'       => $grandTotal,
            'submitted_amount'      => $submittedAmt,
            'verified_amount'       => 0.00,
            'refunded_amount'       => 0.00,
            'delivery_charge'       => $delivFee,
            'cod_amount'            => ($sourceType === PaymentTransaction::SOURCE_COD) ? $grandTotal : 0.00,
            'screenshot_path'       => $screenshot,
            'verification_status'   => $initialStatus,
            'cod_settlement_status' => ($sourceType === PaymentTransaction::SOURCE_COD) ? 'unsettled' : null,
            'created_by'            => $order->user_id ?? (Auth::id() ?: null),
            'submitted_at'          => now(),
        ]);

        self::recordHistory(
            $txn,
            'created',
            null,
            $initialStatus,
            $submittedAmt,
            null,
            $sourceType === PaymentTransaction::SOURCE_COD
                ? 'COD Order placed at checkout.'
                : 'Customer submitted payment request from checkout.'
        );

        return $txn;
    }

    /**
     * ৪. POS Terminal থেকে আসা অর্ডারের ট্রানজেকশন সংরক্ষণ
     */
    public static function recordPosTransaction(object $order, float $paidAmount, string $paymentMethod, ?string $trxId = null): ?PaymentTransaction
    {
        self::ensureTrashColumnExists();
        if (!Schema::hasTable('payment_transactions')) {
            return null;
        }

        self::trashPreviousOrderTransactions((int) $order->id, 'POS Order Transaction');

        $grandTotal = (float) ($order->grand_total ?? ($order->total_amount ?? 0));
        $delivFee   = (float) ($order->shipping_charge ?? ($order->delivery_charge ?? 0));

        if ($paidAmount >= $grandTotal && $grandTotal > 0) {
            $verStatus = PaymentTransaction::STATUS_PAID;
        } elseif ($paidAmount > 0 && $paidAmount < $grandTotal) {
            $verStatus = PaymentTransaction::STATUS_PARTIALLY_PAID;
        } else {
            $verStatus = PaymentTransaction::STATUS_PENDING_VERIFICATION;
        }

        $txn = PaymentTransaction::create([
            'transaction_uid'     => PaymentTransaction::generateUniqueUid(),
            'order_id'            => $order->id,
            'order_number'        => $order->order_number ?? (string) $order->id,
            'invoice_no'          => $order->invoice_no ?? ('POS-' . $order->id),
            'branch_id'           => $order->branch_id ?? null,
            'user_id'             => $order->user_id ?? null,
            'customer_name'       => $order->customer_name ?? ($order->shipping_name ?? 'Walk-in Customer'),
            'customer_phone'      => $order->shipping_phone ?? ($order->phone ?? null),
            'customer_email'      => $order->shipping_email ?? null,
            'source_type'         => PaymentTransaction::SOURCE_POS_CASH,
            'payment_method'      => $paymentMethod,
            'payment_provider'    => $paymentMethod,
            'transaction_id'      => $trxId,
            'normalized_trx_id'   => self::normalizeTrxId($trxId),
            'expected_amount'     => $grandTotal,
            'submitted_amount'    => $paidAmount,
            'verified_amount'     => $paidAmount,
            'delivery_charge'     => $delivFee,
            'verification_status' => $verStatus,
            'created_by'          => Auth::id(),
            'checked_by'          => $paidAmount > 0 ? Auth::id() : null,
            'checked_at'          => $paidAmount > 0 ? now() : null,
            'verified_by'         => $paidAmount > 0 ? Auth::id() : null,
            'verified_at'         => $paidAmount > 0 ? now() : null,
            'admin_remarks'       => 'POS Counter Sale (' . $paymentMethod . ')',
            'submitted_at'        => now(),
        ]);

        self::recordHistory(
            $txn,
            'created',
            null,
            $verStatus,
            $paidAmount,
            null,
            "POS Counter transaction recorded via {$paymentMethod}."
        );

        return $txn;
    }

    /**
     * ৫. Automated Online Gateway (SSLCommerz, bKash PGW, Stripe, PayPal) কলব্যাক সিঙ্ক
     */
    public static function recordGatewayCallback(string $invoiceNo, string $gatewayName, ?string $trxId, string $status, ?float $paidAmount = null): ?PaymentTransaction
    {
        self::ensureTrashColumnExists();
        if (!Schema::hasTable('payment_transactions')) {
            return null;
        }

        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) {
            return null;
        }

        $grandTotal = (float) ($order->grand_total ?? 0);
        $finalPaid  = $paidAmount !== null ? $paidAmount : ($status === 'paid' ? $grandTotal : 0.00);

        $verStatus = match (strtolower($status)) {
            'paid', 'valid', 'completed' => PaymentTransaction::STATUS_PAID,
            'failed'                     => PaymentTransaction::STATUS_FAILED,
            'cancelled', 'canceled'      => PaymentTransaction::STATUS_CANCELLED,
            default                      => PaymentTransaction::STATUS_PENDING_VERIFICATION,
        };

        $existingTxn = PaymentTransaction::where('order_id', $order->id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        if ($existingTxn) {
            $oldStatus = $existingTxn->verification_status;
            $existingTxn->update([
                'source_type'         => PaymentTransaction::SOURCE_GATEWAY,
                'payment_method'      => $gatewayName,
                'payment_provider'    => $gatewayName,
                'transaction_id'      => $trxId ?: $existingTxn->transaction_id,
                'normalized_trx_id'   => self::normalizeTrxId($trxId ?: $existingTxn->transaction_id),
                'submitted_amount'    => $finalPaid > 0 ? $finalPaid : $existingTxn->submitted_amount,
                'verified_amount'     => $verStatus === PaymentTransaction::STATUS_PAID ? $finalPaid : 0.00,
                'verification_status' => $verStatus,
                'verified_at'         => $verStatus === PaymentTransaction::STATUS_PAID ? now() : null,
                'admin_remarks'       => "Automated {$gatewayName} gateway callback ({$verStatus}).",
            ]);

            self::recordHistory(
                $existingTxn,
                'gateway_callback',
                $oldStatus,
                $verStatus,
                $finalPaid,
                null,
                "Gateway [{$gatewayName}] callback updated status to {$verStatus}."
            );

            if ($verStatus === PaymentTransaction::STATUS_PAID) {
                self::recalculateAndSyncOrderBalance($order->id);
            }

            return $existingTxn;
        }

        $newTxn = PaymentTransaction::create([
            'transaction_uid'     => PaymentTransaction::generateUniqueUid(),
            'order_id'            => $order->id,
            'order_number'        => $order->order_number ?? (string) $order->id,
            'invoice_no'          => $order->invoice_no,
            'branch_id'           => $order->branch_id ?? null,
            'user_id'             => $order->user_id ?? null,
            'customer_name'       => $order->customer_name ?? ($order->shipping_name ?? 'Customer'),
            'customer_phone'      => $order->customer_phone ?? ($order->shipping_phone ?? null),
            'customer_email'      => $order->customer_email ?? ($order->shipping_email ?? null),
            'source_type'         => PaymentTransaction::SOURCE_GATEWAY,
            'payment_method'      => $gatewayName,
            'payment_provider'    => $gatewayName,
            'transaction_id'      => $trxId,
            'normalized_trx_id'   => self::normalizeTrxId($trxId),
            'expected_amount'     => $grandTotal,
            'submitted_amount'    => $finalPaid,
            'verified_amount'     => $verStatus === PaymentTransaction::STATUS_PAID ? $finalPaid : 0.00,
            'verification_status' => $verStatus,
            'verified_at'         => $verStatus === PaymentTransaction::STATUS_PAID ? now() : null,
            'admin_remarks'       => "Automated {$gatewayName} gateway transaction.",
            'submitted_at'        => now(),
        ]);

        self::recordHistory($newTxn, 'gateway_callback', null, $verStatus, $finalPaid, null, "Automated {$gatewayName} payment callback.");

        if ($verStatus === PaymentTransaction::STATUS_PAID) {
            self::recalculateAndSyncOrderBalance($order->id);
        }

        return $newTxn;
    }

    /**
     * ৬. অ্যাডমিন কর্তৃক ট্রানজেকশন ভেরিফিকেশন ও স্ট্যাটাস পরিবর্তন
     * (গুরুত্বপূর্ণ ফিক্স: POS বা আগে থেকেই Paid থাকা ট্রানজেকশনকে Checked বা Approve করলে তার Paid স্ট্যাটাস নষ্ট হবে না!)
     */
    public static function executeVerificationAction(
        int $transactionId,
        string $action,
        array $data = []
    ): array {
        self::ensureTrashColumnExists();

        return DB::transaction(function () use ($transactionId, $action, $data) {
            /** @var PaymentTransaction|null $txn */
            $txn = PaymentTransaction::where('id', $transactionId)->lockForUpdate()->first();

            if (!$txn) {
                return ['success' => false, 'message' => 'Transaction record not found.'];
            }

            $oldStatus = $txn->verification_status;
            $adminId   = Auth::id();
            $reason    = isset($data['reason']) ? trim((string) $data['reason']) : null;
            $remarks   = isset($data['remarks']) ? trim((string) $data['remarks']) : null;

            // অভ্যন্তরীণ নোট যোগ করা (স্ট্যাটাস অপরিবর্তিত রেখে)
            if ($action === 'add_note') {
                if (empty($remarks) && empty($reason)) {
                    return ['success' => false, 'message' => 'অনুগ্রহ করে নোট বা মন্তব্য লিখুন।'];
                }
                $noteText = $remarks ?: $reason;
                $txn->admin_remarks = $txn->admin_remarks
                    ? $txn->admin_remarks . "\n[" . date('d M Y h:i A') . "]: " . $noteText
                    : "[" . date('d M Y h:i A') . "]: " . $noteText;
                $txn->updated_by = $adminId;
                $txn->save();

                self::recordHistory($txn, 'note_added', $oldStatus, $oldStatus, (float) $txn->verified_amount, null, $noteText);

                return [
                    'success'     => true,
                    'message'     => 'Internal admin note saved successfully.',
                    'transaction' => $txn->fresh(),
                ];
            }

            // পেইড করার আগে ডুপ্লিকেট TrxID ফাইনাল চেক
            if (in_array($action, ['approve', 'mark_paid', 'mark_partially_paid']) && !empty($txn->transaction_id)) {
                $dupCheck = self::checkDuplicateTransactionId(
                    $txn->transaction_id,
                    $txn->payment_method,
                    $txn->id,
                    $txn->order_id
                );

                if ($dupCheck['is_duplicate']) {
                    $txn->update([
                        'is_duplicate_flagged'        => true,
                        'duplicate_of_transaction_id' => $dupCheck['existing_transaction']->id ?? null,
                        'duplicate_of_order_id'       => $dupCheck['existing_order']->id ?? ($dupCheck['existing_transaction']->order_id ?? null),
                    ]);

                    return [
                        'success' => false,
                        'message' => 'সতর্কতা: ' . ($dupCheck['admin_warning'] ?? $dupCheck['message']),
                    ];
                }
            }

            $newStatus = $oldStatus;

            switch ($action) {
                case 'mark_checked':
                    $txn->checked_by = $adminId;
                    $txn->checked_at = now();

                    // যদি ট্রানজেকশনটি ইতিমধ্যে PAID বা PARTIALLY_PAID থাকে (যেমন POS Cash), তবে সেটিকে নষ্ট করে Checked (Not Paid) করা যাবে না!
                    if (in_array($oldStatus, [PaymentTransaction::STATUS_PAID, PaymentTransaction::STATUS_PARTIALLY_PAID])) {
                        $newStatus        = $oldStatus;
                        $txn->verified_by = $txn->verified_by ?: $adminId;
                        $txn->verified_at = $txn->verified_at ?: now();
                    } else {
                        $newStatus = PaymentTransaction::STATUS_CHECKED;
                    }
                    break;

                case 'approve':
                    $txn->checked_by  = $txn->checked_by ?: $adminId;
                    $txn->checked_at  = $txn->checked_at ?: now();
                    $txn->approved_by = $adminId;
                    $txn->approved_at = now();

                    // আগে থেকেই PAID থাকলে PAID-ই থাকবে
                    if (in_array($oldStatus, [PaymentTransaction::STATUS_PAID, PaymentTransaction::STATUS_PARTIALLY_PAID])) {
                        $newStatus        = $oldStatus;
                        $txn->verified_by = $txn->verified_by ?: $adminId;
                        $txn->verified_at = $txn->verified_at ?: now();
                    } else {
                        $newStatus = PaymentTransaction::STATUS_APPROVED;
                    }
                    break;

                case 'mark_paid':
                    $verifiedAmt = isset($data['verified_amount']) && (float) $data['verified_amount'] > 0
                        ? (float) $data['verified_amount']
                        : ((float) $txn->submitted_amount > 0 ? (float) $txn->submitted_amount : (float) $txn->expected_amount);

                    if ($verifiedAmt <= 0 && (float) $txn->expected_amount > 0) {
                        $verifiedAmt = (float) $txn->expected_amount;
                    }

                    $expectedAmt = (float) $txn->expected_amount;
                    if ($expectedAmt > 0 && $verifiedAmt < $expectedAmt) {
                        $newStatus = PaymentTransaction::STATUS_PARTIALLY_PAID;
                    } else {
                        $newStatus = PaymentTransaction::STATUS_PAID;
                    }

                    $txn->verified_amount = $verifiedAmt;
                    $txn->checked_by      = $txn->checked_by ?: $adminId;
                    $txn->checked_at      = $txn->checked_at ?: now();
                    $txn->approved_by     = $txn->approved_by ?: $adminId;
                    $txn->approved_at     = $txn->approved_at ?: now();
                    $txn->verified_by     = $adminId;
                    $txn->verified_at     = now();

                    if ($txn->source_type === PaymentTransaction::SOURCE_COD) {
                        $txn->cod_settlement_status = ($newStatus === PaymentTransaction::STATUS_PAID) ? 'settled' : 'partial';
                    }
                    break;

                case 'mark_partially_paid':
                    $verifiedAmt = (float) ($data['verified_amount'] ?? $txn->submitted_amount);
                    if ($verifiedAmt <= 0) {
                        return ['success' => false, 'message' => 'আংশিক পরিশোধের (Partial Payment) সঠিক টাকার পরিমাণ দিন।'];
                    }
                    $newStatus            = PaymentTransaction::STATUS_PARTIALLY_PAID;
                    $txn->verified_amount = $verifiedAmt;
                    $txn->checked_by      = $txn->checked_by ?: $adminId;
                    $txn->checked_at      = $txn->checked_at ?: now();
                    $txn->verified_by     = $adminId;
                    $txn->verified_at     = now();
                    break;

                case 'mark_unmatched':
                    if (empty($reason) && empty($remarks)) {
                        return ['success' => false, 'message' => 'Unmatched মার্ক করার জন্য কারণ (Reason) উল্লেখ করা বাধ্যতামূলক।'];
                    }
                    $newStatus             = PaymentTransaction::STATUS_UNMATCHED;
                    $txn->verified_amount  = 0.00;
                    $txn->checked_by       = $txn->checked_by ?: $adminId;
                    $txn->checked_at       = $txn->checked_at ?: now();
                    $txn->rejection_reason = $reason ?: $remarks;
                    break;

                case 'mark_fake':
                    if (empty($reason) && empty($remarks)) {
                        return ['success' => false, 'message' => 'Fake / Rejected মার্ক করার জন্য কারণ (Reason) উল্লেখ করা বাধ্যতামূলক।'];
                    }
                    $newStatus             = PaymentTransaction::STATUS_FAKE;
                    $txn->verified_amount  = 0.00;
                    $txn->checked_by       = $txn->checked_by ?: $adminId;
                    $txn->checked_at       = $txn->checked_at ?: now();
                    $txn->rejection_reason = $reason ?: $remarks;
                    break;

                case 'under_review':
                    $newStatus       = PaymentTransaction::STATUS_UNDER_REVIEW;
                    $txn->checked_by = $txn->checked_by ?: $adminId;
                    $txn->checked_at = $txn->checked_at ?: now();
                    break;

                case 'mark_failed':
                    $newStatus            = PaymentTransaction::STATUS_FAILED;
                    $txn->verified_amount = 0.00;
                    if ($reason) {
                        $txn->rejection_reason = $reason;
                    }
                    break;

                case 'refund':
                    if (empty($reason) && empty($remarks)) {
                        return ['success' => false, 'message' => 'Refund করার জন্য কারণ (Reason) উল্লেখ করা বাধ্যতামূলক।'];
                    }
                    $refundAmt = isset($data['refund_amount']) && (float) $data['refund_amount'] > 0
                        ? (float) $data['refund_amount']
                        : (float) $txn->verified_amount;

                    if ($refundAmt <= 0) {
                        $refundAmt = (float) $txn->submitted_amount;
                    }

                    $newStatus             = PaymentTransaction::STATUS_REFUNDED;
                    $txn->refunded_amount  = $refundAmt;
                    $txn->rejection_reason = $reason ?: $remarks;
                    break;

                default:
                    return ['success' => false, 'message' => "Invalid verification action [{$action}]."];
            }

            if (!empty($remarks)) {
                $txn->admin_remarks = $remarks;
            } elseif (!empty($reason) && empty($txn->admin_remarks)) {
                $txn->admin_remarks = $reason;
            }

            $txn->verification_status = $newStatus;
            $txn->updated_by          = $adminId;
            $txn->save();

            // অডিট ট্রেইল সংরক্ষণ
            self::recordHistory(
                $txn,
                $action,
                $oldStatus,
                $newStatus,
                (float) ($txn->verified_amount > 0 ? $txn->verified_amount : $txn->submitted_amount),
                $reason,
                $remarks
            );

            // গ্লোবাল অ্যাক্টিভিটি লগ
            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('transaction_' . $action, 'payment_transactions', "Transaction #{$txn->transaction_uid} status changed from [{$oldStatus}] to [{$newStatus}]", [
                    'record_id'  => $txn->id,
                    'record_ref' => $txn->invoice_no ?: $txn->transaction_uid,
                    'branch_id'  => $txn->branch_id,
                    'old_values' => ['verification_status' => $oldStatus],
                    'new_values' => [
                        'verification_status' => $newStatus,
                        'verified_amount'     => $txn->verified_amount,
                        'reason'              => $reason,
                    ],
                ]);
            }

            // যদি ট্রানজেকশনটি কোনো অর্ডারের সাথে যুক্ত থাকে, তবে অর্ডারের ব্যালেন্স ও পেমেন্ট স্ট্যাটাস নিরাপদভাবে সিঙ্ক করা
            $orderSyncResult = null;
            if (!empty($txn->order_id)) {
                if (in_array($newStatus, [
                    PaymentTransaction::STATUS_PAID,
                    PaymentTransaction::STATUS_PARTIALLY_PAID,
                    PaymentTransaction::STATUS_UNMATCHED,
                    PaymentTransaction::STATUS_FAKE,
                    PaymentTransaction::STATUS_FAILED,
                    PaymentTransaction::STATUS_REFUNDED,
                ]) || in_array($oldStatus, [
                    PaymentTransaction::STATUS_PAID,
                    PaymentTransaction::STATUS_PARTIALLY_PAID,
                ])) {
                    $orderSyncResult = self::recalculateAndSyncOrderBalance((int) $txn->order_id, $action);
                }
            }

            return [
                'success'     => true,
                'message'     => "Transaction #{$txn->transaction_uid} updated successfully.",
                'transaction' => $txn->fresh(),
                'order_sync'  => $orderSyncResult,
            ];
        });
    }

    /**
     * ৭. Transaction থেকে Order-এর ব্যালেন্স, Due এবং Payment Status নিখুঁতভাবে হিসাব ও সিঙ্ক করা
     * (শুধুমাত্র Non-Trashed ভেরিফাইড ট্রানজেকশন গণনা হবে)
     */
    public static function recalculateAndSyncOrderBalance(int $orderId, ?string $triggeredByAction = null): array
    {
        self::ensureTrashColumnExists();
        if (!Schema::hasTable('orders') || !Schema::hasTable('payment_transactions')) {
            return ['synced' => false];
        }

        $order = DB::table('orders')->where('id', $orderId)->lockForUpdate()->first();
        if (!$order) {
            return ['synced' => false];
        }

        $grandTotal = (float) ($order->grand_total ?? ($order->total_amount ?? 0));

        // শুধুমাত্র Active (deleted_at IS NULL) ট্রানজেকশনগুলো হিসাব করা হবে
        $validTransactions = DB::table('payment_transactions')
            ->where('order_id', $orderId)
            ->whereNull('deleted_at')
            ->whereIn('verification_status', [
                PaymentTransaction::STATUS_PAID,
                PaymentTransaction::STATUS_PARTIALLY_PAID,
                PaymentTransaction::STATUS_REFUNDED,
            ])
            ->get();

        $totalVerified = 0.00;
        $totalRefunded = 0.00;
        $latestValidTrxId = $order->transaction_id ?? null;

        foreach ($validTransactions as $vTxn) {
            if (in_array($vTxn->verification_status, [PaymentTransaction::STATUS_PAID, PaymentTransaction::STATUS_PARTIALLY_PAID])) {
                $totalVerified += (float) $vTxn->verified_amount;
                if (!empty($vTxn->transaction_id)) {
                    $latestValidTrxId = $vTxn->transaction_id;
                }
            }
            $totalRefunded += (float) $vTxn->refunded_amount;
        }

        $netPaid = max(0.00, round($totalVerified - $totalRefunded, 2));
        $dueAmt  = max(0.00, round($grandTotal - $netPaid, 2));
        $overpayment = $netPaid > $grandTotal ? round($netPaid - $grandTotal, 2) : 0.00;

        // Order Payment Status নির্ধারণ
        if ($grandTotal > 0 && $netPaid >= $grandTotal) {
            $newOrderPaymentStatus = 'paid';
        } elseif ($netPaid > 0 && $netPaid < $grandTotal) {
            $newOrderPaymentStatus = 'partial';
        } elseif ($totalRefunded > 0 && $netPaid <= 0) {
            $newOrderPaymentStatus = 'refunded';
        } else {
            $newOrderPaymentStatus = 'unpaid';
        }

        $updateOrder = [
            'payment_status' => $newOrderPaymentStatus,
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('orders', 'paid_amount')) {
            $updateOrder['paid_amount'] = $netPaid;
        }
        if (Schema::hasColumn('orders', 'due_amount')) {
            $updateOrder['due_amount'] = $dueAmt;
        }
        if (!empty($latestValidTrxId) && Schema::hasColumn('orders', 'transaction_id')) {
            $updateOrder['transaction_id'] = $latestValidTrxId;
        }

        // যদি সম্পূর্ণ Paid হয় এবং অর্ডারটি এখনো pending থাকে, তবে স্ট্যাটাস processing/confirmed করা
        $currentOrderStatus = strtolower($order->order_status ?? 'pending');
        if ($newOrderPaymentStatus === 'paid' && $currentOrderStatus === 'pending') {
            $updateOrder['order_status'] = 'processing';
        }

        DB::table('orders')->where('id', $orderId)->update($updateOrder);

        // যদি কুরিয়ার বুকিং থাকে এবং COD ট্রানজেকশন সেটেল হয়, তবে কুরিয়ার টেবিলও সিঙ্ক করা
        if ($newOrderPaymentStatus === 'paid' && Schema::hasTable('courier_bookings')) {
            DB::table('courier_bookings')
                ->where('order_id', $orderId)
                ->update([
                    'cod_settlement_status' => 'settled',
                    'settled_amount'        => DB::raw('cod_amount'),
                    'settlement_date'       => now(),
                    'updated_at'            => now(),
                ]);
        }

        // যদি সম্পূর্ণ Paid হয়, তবে ডিজিটাল পণ্য থাকলে তার লাইসেন্স/ডাউনলোড আনলক করা
        if ($newOrderPaymentStatus === 'paid' && class_exists(DigitalProductService::class)) {
            try {
                DigitalProductService::fulfillDigitalOrder($orderId);
            } catch (\Throwable $e) {
                Log::error("Digital fulfillment sync error for Order #{$orderId}: " . $e->getMessage());
            }
        }

        return [
            'synced'                  => true,
            'order_id'                => $orderId,
            'order_payment_status'    => $newOrderPaymentStatus,
            'net_paid_amount'         => $netPaid,
            'due_amount'              => $dueAmt,
            'overpayment_warning_amt' => $overpayment,
        ];
    }

    /**
     * ৮. Order Management থেকে যখন অ্যাডমিন Order Status বা Payment Status পরিবর্তন করে তখন Transaction-এর সাথে সিঙ্ক
     */
    public static function syncFromOrderUpdate(
        int $orderId,
        string $targetOrderStatus,
        string $targetPaymentStatus,
        ?string $inputTrxId = null,
        ?string $adminNote = null
    ): array {
        self::ensureTrashColumnExists();
        if (!Schema::hasTable('payment_transactions')) {
            return ['allowed' => true];
        }

        $order = DB::table('orders')->where('id',$orderId)->first();
        if (!$order) {
            return ['allowed' => false, 'message' => 'Order not found.'];
        }

        $targetPaymentStatus = strtolower(trim($targetPaymentStatus));
        $targetOrderStatus   = strtolower(trim($targetOrderStatus));

        // যদি নতুন Transaction ID দেওয়া হয়ে থাকে, তবে সেটির ডুপ্লিকেট চেক
        if (!empty($inputTrxId)) {
            $dupCheck = self::checkDuplicateTransactionId($inputTrxId, $order->payment_method ?? null, null, $orderId);
            if ($dupCheck['is_duplicate']) {
                return [
                    'allowed' => false,
                    'message' => $dupCheck['admin_warning'] ?? $dupCheck['message'],
                ];
            }
        }

        $transactions = PaymentTransaction::where('order_id',$orderId)->whereNull('deleted_at')->orderByDesc('id')->get();

        // সেফটি গার্ড: যদি অর্ডারের একমাত্র ট্রানজেকশন Fake বা Unmatched হিসেবে চিহ্নিত থাকে, তবে ভুল করে Order থেকে সরাসরি Paid করা যাবে না!
        if ($targetPaymentStatus === 'paid' &&$transactions->isNotEmpty()) {
            $hasVerifiedOrPending =$transactions->whereIn('verification_status', [
                PaymentTransaction::STATUS_PAID,
                PaymentTransaction::STATUS_PARTIALLY_PAID,
                PaymentTransaction::STATUS_APPROVED,
                PaymentTransaction::STATUS_CHECKED,
                PaymentTransaction::STATUS_PENDING_VERIFICATION,
            ])->isNotEmpty();

            $hasFakeOrUnmatched =$transactions->whereIn('verification_status', [
                PaymentTransaction::STATUS_FAKE,
                PaymentTransaction::STATUS_UNMATCHED,
            ])->isNotEmpty();

            if (!$hasVerifiedOrPending &&$hasFakeOrUnmatched) {
                return [
                    'allowed' => false,
                    'message' => 'নিরাপত্তা বাধা: এই অর্ডারের ট্রানজেকশনটি Fake / Unmatched হিসেবে চিহ্নিত রয়েছে। ট্রানজেকশন ভেরিফিকেশন ছাড়া সরাসরি অর্ডারকে Paid করা যাবে না।'
                ];
            }
        }

        $grandTotal = (float) ($order->grand_total ?? 0);
        $adminId    = Auth::id();

        if ($targetPaymentStatus === 'paid') {
            $candidateTxn =$transactions->first();

            if ($candidateTxn) {
                $oldSt =$candidateTxn->verification_status;
                $candidateTxn->update([
                    'transaction_id'        => $inputTrxId ?: $candidateTxn->transaction_id,
                    'normalized_trx_id'     => self::normalizeTrxId($inputTrxId ?: $candidateTxn->transaction_id),
                    'verified_amount'       => $grandTotal,
                    'verification_status'   => PaymentTransaction::STATUS_PAID,
                    'cod_settlement_status' => $candidateTxn->source_type === PaymentTransaction::SOURCE_COD ? 'settled' : $candidateTxn->cod_settlement_status,
                    'checked_by'            => $candidateTxn->checked_by ?: $adminId,
                    'checked_at'            => $candidateTxn->checked_at ?: now(),
                    'verified_by'           => $adminId,
                    'verified_at'           => now(),
                    'updated_by'            => $adminId,
                    'admin_remarks'         => $adminNote ?: ($candidateTxn->admin_remarks ?: 'Verified & marked Paid from Order Management.'),
                ]);

                self::recordHistory(
                    $candidateTxn,
                    'order_sync',
                    $oldSt,
                    PaymentTransaction::STATUS_PAID,
                    $grandTotal,
                    null,
                    'Synchronized as Paid from Order Management.'
                );
            } else {
                $payMethod =$order->payment_method ?? 'Cash on Delivery';
                $srcType   = in_array(strtolower($payMethod), ['cod', 'cash on delivery'])
                    ? PaymentTransaction::SOURCE_COD
                    : PaymentTransaction::SOURCE_MANUAL;

                $newTxn = PaymentTransaction::create([
                    'transaction_uid'       => PaymentTransaction::generateUniqueUid(),
                    'order_id'              => $order->id,
                    'order_number'          => $order->order_number ?? (string) $order->id,
                    'invoice_no'            => $order->invoice_no ?? ('ORD-' . $order->id),
                    'branch_id'             => $order->branch_id ?? null,
                    'user_id'               => $order->user_id ?? null,
                    'customer_name'         => $order->customer_name ?? ($order->shipping_name ?? 'Customer'),
                    'customer_phone'        => $order->customer_phone ?? ($order->shipping_phone ?? null),
                    'customer_email'        => $order->customer_email ?? ($order->shipping_email ?? null),
                    'source_type'           => $srcType,
                    'payment_method'        => $payMethod,
                    'payment_provider'      => $payMethod,
                    'transaction_id'        => $inputTrxId ?: ($order->transaction_id ?? null),
                    'normalized_trx_id'     => self::normalizeTrxId($inputTrxId ?: ($order->transaction_id ?? null)),
                    'expected_amount'       => $grandTotal,
                    'submitted_amount'      => $grandTotal,
                    'verified_amount'       => $grandTotal,
                    'verification_status'   => PaymentTransaction::STATUS_PAID,
                    'cod_settlement_status' => $srcType === PaymentTransaction::SOURCE_COD ? 'settled' : null,
                    'created_by'            => $adminId,
                    'checked_by'            => $adminId,
                    'checked_at'            => now(),
                    'verified_by'           => $adminId,
                    'verified_at'           => now(),
                    'admin_remarks'         => $adminNote ?: 'Manual paid entry created from Order Management.',
                    'submitted_at'          => now(),
                ]);

                self::recordHistory(
                    $newTxn,
                    'order_sync',
                    null,
                    PaymentTransaction::STATUS_PAID,
                    $grandTotal,
                    null,
                    'Created & verified via Order Status Control.'
                );
            }
        }

        return ['allowed' => true];
    }

    /**
     * ৯. অ্যাডমিন কর্তৃক ম্যানুয়াল ট্রানজেকশন এন্ট্রি
     * (নিয়ম: শুধুমাত্র ডাটাবেজে থাকা Online Recorded Order-এর জন্য ট্রানজেকশন অ্যাড করা যাবে এবং আগের ট্রানজেকশন থাকলে সেটি Trash-এ চলে যাবে)
     */
    public static function createManualTransaction(array $data): array
    {
        self::ensureTrashColumnExists();

        return DB::transaction(function () use ($data) {$rawTrx = !empty($data['transaction_id']) ? trim((string) $data['transaction_id']) : null;
            $method = trim((string) ($data['payment_method'] ?? ''));

            // ১. ডাটাবেজে থাকা বৈধ অর্ডার যাচাই (অর্ডার ছাড়া ট্রানজেকশন অ্যাড করা যাবে না)
            $order = null;
            if (!empty($data['order_id'])) {
                $order = DB::table('orders')->where('id', (int)$data['order_id'])->lockForUpdate()->first();
            } elseif (!empty($data['invoice_no'])) {
                $cleanInv = ltrim(trim((string)$data['invoice_no']), '#');
                $order = DB::table('orders')
                    ->where('invoice_no', $cleanInv)
                    ->orWhere('id', $cleanInv)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'ডাটাবেজে এই অর্ডারটি পাওয়া যায়নি! শুধুমাত্র সিস্টেমে রেকর্ড থাকা অর্ডারের জন্য ট্রানজেকশন যোগ করা যাবে। আগে অর্ডার তৈরি করুন, তারপর ট্রানজেকশন যুক্ত করুন।',
                ];
            }

            // ২. ডুপ্লিকেট Transaction ID যাচাই (অন্য কোনো অর্ডারে ব্যবহৃত হয়েছে কি না)
            if (!empty($rawTrx)) {
                $dupCheck = self::checkDuplicateTransactionId($rawTrx, $method, null, (int)$order->id);
                if ($dupCheck['is_duplicate']) {
                    return [
                        'success' => false,
                        'message' => $dupCheck['admin_warning'] ?? $dupCheck['message'],
                    ];
                }
            }

            // ৩. এই অর্ডারের আগের কোনো ট্রানজেকশন থাকলে সেটিকে স্বয়ংক্রিয়ভাবে Trash-এ পাঠানো
            $trashedPrevCount = self::trashPreviousOrderTransactions(
                (int) $order->id,
                'Replaced by new Manual Transaction entry'
            );

            $amount      = (float) ($data['amount'] ?? 0);$expectedAmt = (float) ($order->grand_total ?? $amount);
            $verStatus   =$data['verification_status'] ?? PaymentTransaction::STATUS_PAID;
            $adminId     = Auth::id();

            $isVerified = in_array($verStatus, [PaymentTransaction::STATUS_PAID, PaymentTransaction::STATUS_PARTIALLY_PAID]);

            $custName  = !empty($data['customer_name']) ? trim((string) $data['customer_name']) : ($order->customer_name ?? ($order->shipping_name ?? 'Customer'));
            $custPhone = !empty($data['customer_phone']) ? trim((string) $data['customer_phone']) : ($order->customer_phone ?? ($order->shipping_phone ?? null));
            $custEmail = !empty($data['customer_email']) ? trim((string) $data['customer_email']) : ($order->customer_email ?? ($order->shipping_email ?? null));

            $txn = PaymentTransaction::create([
                'transaction_uid'     => PaymentTransaction::generateUniqueUid(),
                'order_id'            => $order->id,
                'order_number'        => $order->order_number ?? (string) $order->id,
                'invoice_no'          => $order->invoice_no ?? ('ORD-' . $order->id),
                'branch_id'           => $data['branch_id'] ?? ($order->branch_id ?? (Auth::user()->branch_id ?? null)),
                'user_id'             => $order->user_id ?? ($data['user_id'] ?? null),
                'customer_name'       => $custName,
                'customer_phone'      => $custPhone,
                'customer_email'      => $custEmail,
                'source_type'         => PaymentTransaction::SOURCE_MANUAL,
                'payment_method'      => $method,
                'payment_provider'    => $method,
                'sender_number'       => !empty($data['sender_number']) ? trim((string) $data['sender_number']) : null,
                'transaction_id'      => $rawTrx,
                'normalized_trx_id'   => self::normalizeTrxId($rawTrx),
                'expected_amount'     => $expectedAmt,
                'submitted_amount'    => $amount,
                'verified_amount'     => $isVerified ? $amount : 0.00, 'screenshot_path'     =>$data['screenshot_path'] ?? null,
                'verification_status' => $verStatus,
                'created_by'          => $adminId,
                'checked_by'          => $adminId,
                'checked_at'          => now(),
                'verified_by'         => $isVerified ? $adminId : null,
                'verified_at'         => $isVerified ? now() : null,
                'admin_remarks'       => $data['remarks'] ?? 'Manual transaction recorded by admin.',
                'submitted_at'        => !empty($data['transaction_date']) ?$data['transaction_date'] : now(),
            ]);

            self::recordHistory(
                $txn,
                'created',
                null,
                $verStatus,$amount,
                null,
                ($data['remarks'] ?? 'Manual transaction entry created by staff/admin.') . ($trashedPrevCount > 0 ? " (Previous transaction moved to Trash)." : "")
            );

            // অর্ডারের ব্যালেন্স এবং স্ট্যাটাস পুনরায় হিসাব ও সিঙ্ক করা
            $orderSync = self::recalculateAndSyncOrderBalance((int)$order->id, 'manual_entry');

            $msg = "Manual transaction #{$txn->transaction_uid} recorded for Order #{$txn->invoice_no}.";
            if ($trashedPrevCount > 0) {$msg .= " Previous transaction record for this order was automatically moved to Trash to prevent duplicate totals.";
            }

            return [
                'success'     => true,
                'message'     => $msg,
                'transaction' => $txn,
                'order_sync'  => $orderSync,
            ];
        });
    }

    /**
     * ১০. Bulk Actions, Single Trash, Restore & Permanent Delete Engine
     */
    public static function executeBulkActions(array $ids, string $action, ?string $reason = null): array
    {
        self::ensureTrashColumnExists();

        return DB::transaction(function () use ($ids, $action,$reason) {
            $txns = PaymentTransaction::whereIn('id',$ids)->lockForUpdate()->get();
            if ($txns->isEmpty()) {
                return ['success' => false, 'message' => 'No matching transactions found.'];
            }

            $processed = 0;
            $affectedOrderIds = [];

            foreach ($txns as$txn) {
                if (!empty($txn->order_id)) {
                    $affectedOrderIds[] = (int)$txn->order_id;
                }

                if ($action === 'trash') {
                    if ($txn->deleted_at === null) {
                        $oldSt =$txn->verification_status;
                        $txn->update([
                            'deleted_at' => now(),
                            'updated_by' => Auth::id(),
                        ]);
                        self::recordHistory($txn, 'moved_to_trash',$oldSt, 'trashed', (float) $txn->verified_amount,$reason ?: 'Moved to Trash', 'Transaction moved to trash by admin.');
                        $processed++;
                    }
                } elseif ($action === 'restore') {
                    if ($txn->deleted_at !== null) {
                        // যদি ওই অর্ডারের অন্য কোনো Active ট্রানজেকশন থাকে, তবে সেটিকে আগে ট্র্যাশে পাঠিয়ে এটি রিস্টোর করা হবে যাতে ডুপ্লিকেট না হয়
                        if (!empty($txn->order_id)) {
                            PaymentTransaction::where('order_id', $txn->order_id)
                                ->where('id', '!=', $txn->id)
                                ->whereNull('deleted_at')
                                ->update(['deleted_at' => now()]);
                        }
                        $txn->update([
                            'deleted_at' => null,
                            'updated_by' => Auth::id(),
                        ]);
                        self::recordHistory($txn, 'restored', 'trashed',$txn->verification_status, (float) $txn->verified_amount, null, 'Restored from Trash by admin.');$processed++;
                    }
                } elseif ($action === 'force_delete') {
                    DB::table('payment_verification_histories')->where('payment_transaction_id', $txn->id)->delete();
                    $txn->delete();$processed++;
                } elseif (in_array($action, ['mark_checked', 'approve', 'mark_paid', 'under_review', 'mark_unmatched', 'mark_fake'])) {$res = self::executeVerificationAction($txn->id,$action, [
                        'reason'  => $reason ?: 'Bulk Action: ' . strtoupper(str_replace('_', ' ', $action)),
                        'remarks' => $reason ?: 'Bulk Action: ' . strtoupper(str_replace('_', ' ', $action)),
                    ]);
                    if ($res['success']) {$processed++;
                    }
                }
            }

            foreach (array_unique($affectedOrderIds) as$ordId) {
                self::recalculateAndSyncOrderBalance($ordId, 'bulk_' .$action);
            }

            return [
                'success' => true,
                'message' => "Successfully processed {$processed} transaction(s) with action [" . strtoupper(str_replace('_', ' ', $action)) . "].",
            ];
        });
    }

    /**
     * ১১. অ্যাডমিন সাইডবার ও হেডারের জন্য একক ও নির্ভুল Unchecked / Pending Verification Alert Counter (Trash বাদে)
     * (ফিক্স: আগে `OR (checked_by IS NULL AND verified_by IS NULL)` থাকার কারণে পেজ রিলোড ও AJAX-এ দুই রকম সংখ্যা দেখাত। এখন শুধুমাত্র প্রকৃত `pending_verification` এবং `under_review` গণনা হবে।)
     */
    public static function getUncheckedAlertsSummary(): array
    {
        self::ensureTrashColumnExists();
        if (!Schema::hasTable('payment_transactions')) {
            return [
                'pending_checkout_count' => 0,
                'total_unchecked_count'  => 0,
                'under_review_count'     => 0,
                'duplicate_flag_count'   => 0,
                'unmatched_fake_count'   => 0,
                'trashed_count'          => 0,
            ];
        }

        try {
            $stats = DB::table('payment_transactions')
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN deleted_at IS NULL AND source_type IN ('checkout', 'gateway') AND verification_status = 'pending_verification' THEN 1 ELSE 0 END), 0) as pending_checkout,
                    COALESCE(SUM(CASE WHEN deleted_at IS NULL AND verification_status IN ('pending_verification', 'under_review') THEN 1 ELSE 0 END), 0) as total_unchecked,
                    COALESCE(SUM(CASE WHEN deleted_at IS NULL AND verification_status = 'under_review' THEN 1 ELSE 0 END), 0) as under_review,
                    COALESCE(SUM(CASE WHEN deleted_at IS NULL AND is_duplicate_flagged = 1 THEN 1 ELSE 0 END), 0) as duplicate_flagged,
                    COALESCE(SUM(CASE WHEN deleted_at IS NULL AND verification_status IN ('unmatched', 'fake') THEN 1 ELSE 0 END), 0) as unmatched_fake,
                    COALESCE(SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END), 0) as trashed_total
                ")
                ->first();

            return [
                'pending_checkout_count' => (int) ($stats->pending_checkout ?? 0),
                'total_unchecked_count'  => (int) ($stats->total_unchecked ?? 0),
                'under_review_count'     => (int) ($stats->under_review ?? 0),
                'duplicate_flag_count'   => (int) ($stats->duplicate_flagged ?? 0),
                'unmatched_fake_count'   => (int) ($stats->unmatched_fake ?? 0),
                'trashed_count'          => (int) ($stats->trashed_total ?? 0),
            ];
        } catch (\Throwable $e) {
            return [
                'pending_checkout_count' => 0,
                'total_unchecked_count'  => 0,
                'under_review_count'     => 0,
                'duplicate_flag_count'   => 0,
                'unmatched_fake_count'   => 0,
                'trashed_count'          => 0,
            ];
        }
    }

    /**
     * ১২. Immutable Verification History লগ লেখার মেথড
     */
    public static function recordHistory(
        PaymentTransaction $txn,
        string $actionType,
        ?string $oldStatus,
        ?string $newStatus,
        float $amount = 0.00,
        ?string $reason = null,
        ?string $remarks = null
    ): void {
        if (!Schema::hasTable('payment_verification_histories')) {
            return;
        }

        $user = Auth::user();$req  = request();

        PaymentVerificationHistory::create([
            'payment_transaction_id' => $txn->id,
            'order_id'               => $txn->order_id,
            'invoice_no'             => $txn->invoice_no,
            'action_type'            => $actionType,
            'old_status'             => $oldStatus,
            'new_status'             => $newStatus,
            'amount'                 => $amount,
            'payment_method'         => $txn->payment_method,
            'transaction_id'         => $txn->transaction_id,
            'reason'                 => $reason,
            'remarks'                => $remarks,
            'performed_by'           => $user->id ?? null,
            'performed_by_name'      => $user->name ?? ($txn->customer_name ?: 'Customer / System'),
            'performed_by_role'      => $user->role ?? 'customer',
            'ip_address'             => $req ? $req->ip() : null,
            'user_agent'             => $req ? substr((string) $req->userAgent(), 0, 490) : null,
        ]);
    }
}
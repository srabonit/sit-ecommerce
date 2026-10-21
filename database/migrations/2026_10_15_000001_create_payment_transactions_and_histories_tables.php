<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ১. মূল Payment Transactions টেবিল তৈরি
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_uid', 40)->unique()->comment('System generated unique reference  TXN-20261015-XXXX');
                
                // Order & Branch Relations
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('order_number', 60)->nullable()->index();
                $table->string('invoice_no', 60)->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                
                // Customer Details
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('customer_name', 150)->nullable();
                $table->string('customer_phone', 30)->nullable()->index();
                $table->string('customer_email', 150)->nullable()->index();

                // Source & Method Classification
                // source_type: checkout, pos_cash, cod, manual, gateway
                $table->string('source_type', 30)->default('checkout')->index();
                $table->string('payment_method', 60)->index();
                $table->string('payment_provider', 60)->nullable();
                $table->string('sender_number', 30)->nullable()->index();
                
                // Transaction ID & Duplicate Prevention Columns
                $table->string('transaction_id', 100)->nullable()->index();
                $table->string('normalized_trx_id', 100)->nullable()->index()->comment('Uppercase alphanumeric normalized TrxID for strict duplicate detection');
                $table->boolean('is_duplicate_flagged')->default(false)->index();
                $table->unsignedBigInteger('duplicate_of_transaction_id')->nullable();
                $table->unsignedBigInteger('duplicate_of_order_id')->nullable();

                // Financial Amounts
                $table->decimal('expected_amount', 12, 2)->default(0.00);
                $table->decimal('submitted_amount', 12, 2)->default(0.00);
                $table->decimal('verified_amount', 12, 2)->default(0.00);
                $table->decimal('refunded_amount', 12, 2)->default(0.00);
                $table->decimal('delivery_charge', 12, 2)->default(0.00);
                $table->decimal('cod_amount', 12, 2)->default(0.00);

                // Attachment / Proof
                $table->string('screenshot_path', 500)->nullable();

                // Granular Verification Status:
                // pending_verification, checked, approved, paid, partially_paid, unmatched, fake, under_review, failed, cancelled, refunded
                $table->string('verification_status', 35)->default('pending_verification')->index();
                $table->string('cod_settlement_status', 30)->nullable()->index()->comment('unsettled, partial, settled');

                // Actors & Audit Timestamps
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('checked_by')->nullable()->index();
                $table->timestamp('checked_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable()->index();
                $table->timestamp('verified_at')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable();

                // Notes & Reasons
                $table->text('admin_remarks')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->json('meta_data')->nullable();

                $table->timestamp('submitted_at')->nullable()->index();
                $table->timestamps();

                // Composite Indexes for Fast AJAX Filtering
                $table->index(['source_type', 'verification_status']);
                $table->index(['payment_method', 'normalized_trx_id']);
                $table->index(['created_at', 'verification_status']);
            });
        }

        // ২. Payment Verification Histories (Immutable Audit Trail) টেবিল তৈরি
        if (!Schema::hasTable('payment_verification_histories')) {
            Schema::create('payment_verification_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_transaction_id')->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('invoice_no', 60)->nullable();
                $table->string('action_type', 50)->index()->comment('created, mark_checked, approve, mark_paid, mark_unmatched, mark_fake, under_review, refund, note_added, order_sync');
                $table->string('old_status', 35)->nullable();
                $table->string('new_status', 35)->nullable();
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->string('payment_method', 60)->nullable();
                $table->string('transaction_id', 100)->nullable();
                $table->text('reason')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('performed_by')->nullable()->index();
                $table->string('performed_by_name', 120)->nullable();
                $table->string('performed_by_role', 60)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamps();
            });
        }

        // ৩. বিদ্যমান Orders টেবিলের পুরোনো পেমেন্ট রেকর্ডগুলো নিরাপদভাবে Backfill করা
        if (Schema::hasTable('orders') && Schema::hasTable('payment_transactions')) {
            $existingCount = DB::table('payment_transactions')->count();
            if ($existingCount === 0) {
                $orders = DB::table('orders')->orderBy('id', 'asc')->get();

                foreach ($orders as $ord) {
                    $orderSource = strtolower($ord->order_source ?? ($ord->order_type ?? 'online'));
                    $payMethod   = trim((string)($ord->payment_method ?? 'Cash on Delivery'));
                    $payMethodLower = strtolower($payMethod);
                    $ordPayStatus = strtolower($ord->payment_status ?? 'unpaid');
                    $ordStatus    = strtolower($ord->order_status ?? 'pending');

                    // Source Type নির্ধারণ
                    if ($orderSource === 'pos') {
                        $sourceType = 'pos_cash';
                    } elseif (in_array($payMethodLower, ['cod', 'cash on delivery'])) {
                        $sourceType = 'cod';
                    } elseif (str_contains($payMethodLower, 'ssl') || str_contains($payMethodLower, 'stripe') || str_contains($payMethodLower, 'paypal')) {
                        $sourceType = 'gateway';
                    } else {
                        $sourceType = 'checkout';
                    }

                    $grandTotal = (float)($ord->grand_total ?? ($ord->total_amount ?? 0));
                    $paidAmt    = (float)($ord->paid_amount ?? ($ordPayStatus === 'paid' ? $grandTotal : 0));
                    $delivFee   = (float)($ord->delivery_charge ?? ($ord->shipping_charge ?? 0));

                    // Verification Status নির্ধারণ
                    if ($ordPayStatus === 'paid') {
                        $verStatus = 'paid';
                        $verifiedAmt = $paidAmt > 0 ? $paidAmt : $grandTotal;
                    } elseif ($ordPayStatus === 'partial') {
                        $verStatus = 'partially_paid';
                        $verifiedAmt = $paidAmt;
                    } elseif ($ordPayStatus === 'failed') {
                        $verStatus = 'failed';
                        $verifiedAmt = 0;
                    } elseif ($ordStatus === 'cancelled') {
                        $verStatus = 'cancelled';
                        $verifiedAmt = 0;
                    } else {
                        $verStatus = 'pending_verification';
                        $verifiedAmt = 0;
                    }

                    $rawTrx = !empty($ord->transaction_id) ? trim((string)$ord->transaction_id) : null;
                    $normTrx = $rawTrx ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawTrx)) : null;

                    $custName  = $ord->shipping_name ?? ($ord->customer_name ?? ($ord->name ?? 'Guest Customer'));
                    $custPhone = $ord->shipping_phone ?? ($ord->customer_phone ?? ($ord->phone ?? null));
                    $custEmail = $ord->shipping_email ?? ($ord->customer_email ?? ($ord->email ?? null));
                    $invNo     = $ord->invoice_no ?? ($ord->order_number ?? ('ORD-' . $ord->id));

                    $uid = 'TXN-' . date('Ymd', strtotime($ord->created_at ?? now())) . '-' . strtoupper(Str::random(6)) . $ord->id;

                    $txnId = DB::table('payment_transactions')->insertGetId([
                        'transaction_uid'       => substr($uid, 0, 40),
                        'order_id'              => $ord->id,
                        'order_number'          => $ord->order_number ?? (string)$ord->id,
                        'invoice_no'            => $invNo,
                        'branch_id'             => $ord->branch_id ?? null,
                        'user_id'               => $ord->user_id ?? null,
                        'customer_name'         => $custName,
                        'customer_phone'        => $custPhone,
                        'customer_email'        => $custEmail,
                        'source_type'           => $sourceType,
                        'payment_method'        => $payMethod,
                        'payment_provider'      => $payMethod,
                        'sender_number'         => $ord->sender_number ?? null,
                        'transaction_id'        => $rawTrx,
                        'normalized_trx_id'     => $normTrx,
                        'is_duplicate_flagged'  => false,
                        'expected_amount'       => $grandTotal,
                        'submitted_amount'      => ($sourceType === 'checkout' && $rawTrx) ? $grandTotal : $paidAmt,
                        'verified_amount'       => $verifiedAmt,
                        'refunded_amount'       => 0.00,
                        'delivery_charge'       => $delivFee,
                        'cod_amount'            => ($sourceType === 'cod') ? max(0, $grandTotal - $verifiedAmt) : 0.00,
                        'screenshot_path'       => $ord->payment_screenshot ?? null,
                        'verification_status'   => $verStatus,
                        'cod_settlement_status' => ($sourceType === 'cod') ? ($verStatus === 'paid' ? 'settled' : 'unsettled') : null,
                        'verified_at'           => ($verStatus === 'paid') ? ($ord->updated_at ?? now()) : null,
                        'admin_remarks'         => $ord->admin_note ?? null,
                        'submitted_at'          => $ord->created_at ?? now(),
                        'created_at'            => $ord->created_at ?? now(),
                        'updated_at'            => $ord->updated_at ?? now(),
                    ]);

                    DB::table('payment_verification_histories')->insert([
                        'payment_transaction_id' => $txnId,
                        'order_id'               => $ord->id,
                        'invoice_no'             => $invNo,
                        'action_type'            => 'initial_migration_sync',
                        'old_status'             => null,
                        'new_status'             => $verStatus,
                        'amount'                 => $grandTotal,
                        'payment_method'         => $payMethod,
                        'transaction_id'         => $rawTrx,
                        'remarks'                => 'Automatically synchronized from existing orders table during system upgrade.',
                        'performed_by_name'      => 'System Migration',
                        'performed_by_role'      => 'system',
                        'created_at'             => $ord->created_at ?? now(),
                        'updated_at'             => $ord->updated_at ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_verification_histories');
        Schema::dropIfExists('payment_transactions');
    }
};
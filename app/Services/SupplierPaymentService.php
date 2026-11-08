<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SupplierPaymentService
{
    protected SupplierLedgerService $ledgerService;

    public function __construct(SupplierLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Create a supplier payment voucher, process bill-by-bill allocations,
     * update purchase balances, and post entries to the supplier ledger.
     *
     * @param array $data [
     *     'supplier_id', 'payment_date', 'payment_type', 'bank_account_id',
     *     'payment_method', 'amount', 'transaction_reference', 'cheque_number',
     *     'cheque_date', 'attachment_file', 'notes',
     *     'allocations' => [
     *         ['purchase_id' => int, 'amount' => float, 'remarks' => string|null]
     *     ]
     * ]
     * @return SupplierPayment
     * @throws Exception
     */
    public function makePayment(array $data): SupplierPayment
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = round((float) ($data['amount'] ?? 0), 2);
            if ($totalAmount <= 0) {
                throw new InvalidArgumentException('Payment amount must be greater than zero.');
            }

            $paymentType = $data['payment_type'] ?? 'invoice_payment';
            $allocationsInput = $data['allocations'] ?? [];
            $totalAllocated = 0.00;

            // Validate total allocation doesn't exceed payment amount
            foreach ($allocationsInput as $alloc) {
                $totalAllocated += round((float) ($alloc['amount'] ?? 0), 2);
            }

            if ($totalAllocated > $totalAmount) {
                throw new InvalidArgumentException('Total allocated amount cannot exceed total payment voucher amount.');
            }

            $unallocatedAmount = round($totalAmount - $totalAllocated, 2);

            // Create Master Payment Voucher
            $payment = SupplierPayment::create([
                'payment_number'        => $this->generatePaymentNumber(),
                'supplier_id'           => $data['supplier_id'],
                'payment_date'          => $data['payment_date'] ?? now()->toDateString(),
                'payment_type'          => $paymentType,
                'bank_account_id'       => $data['bank_account_id'] ?? null,
                'payment_method'        => $data['payment_method'] ?? 'cash',
                'amount'                => $totalAmount,
                'allocated_amount'      => $totalAllocated,
                'unallocated_amount'    => $unallocatedAmount,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'cheque_number'         => $data['cheque_number'] ?? null,
                'cheque_date'           => $data['cheque_date'] ?? null,
                'attachment_file'       => $data['attachment_file'] ?? null,
                'status'                => $data['status'] ?? 'approved',
                'created_by'            => Auth::id() ?? ($data['created_by'] ?? null),
                'approved_by'           => $data['approved_by'] ?? Auth::id(),
                'notes'                 => $data['notes'] ?? null,
            ]);

            // Process purchase allocations with row-level locks
            foreach ($allocationsInput as $allocItem) {
                $allocAmount = round((float) ($allocItem['amount'] ?? 0), 2);
                if ($allocAmount <= 0) {
                    continue;
                }

                $purchase = Purchase::where('id', $allocItem['purchase_id'])
                    ->where('supplier_id', $data['supplier_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($allocAmount > (float) $purchase->due_amount) {
                    throw new Exception("Allocated amount ({$allocAmount}) exceeds outstanding due ({$purchase->due_amount}) for bill #{$purchase->purchase_number}.");
                }

                $payment->allocations()->create([
                    'purchase_id'      => $purchase->id,
                    'allocated_amount' => $allocAmount,
                    'remarks'          => $allocItem['remarks'] ?? null,
                ]);

                // Recalculate Purchase Paid and Due Balances
                $newPaid = round((float) $purchase->paid_amount + $allocAmount, 2);
                $newDue = max(0.00, round((float) $purchase->grand_total - $newPaid, 2));

                $paymentStatus = 'partially_paid';
                if ($newDue <= 0.00) {
                    $paymentStatus = 'paid';
                }

                $purchase->update([
                    'paid_amount'    => $newPaid,
                    'due_amount'     => $newDue,
                    'payment_status' => $paymentStatus,
                ]);
            }

            // Sync with Supplier Ledger (debit entry decreasing liability)
            $this->ledgerService->recordPayment($payment);

            return $payment->load(['allocations.purchase', 'supplier']);
        });
    }

    /**
     * Settle unallocated advance funds from an existing payment voucher
     * against outstanding purchase invoices.
     *
     * @param SupplierPayment $payment
     * @param array $allocations [ ['purchase_id' => int, 'amount' => float, 'remarks' => string|null] ]
     * @return SupplierPayment
     * @throws Exception
     */
    public function allocateAdvanceBalance(SupplierPayment $payment, array $allocations): SupplierPayment
    {
        return DB::transaction(function () use ($payment, $allocations) {
            $lockedPayment = SupplierPayment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            $availableUnallocated = (float) $lockedPayment->unallocated_amount;
            if ($availableUnallocated <= 0) {
                throw new Exception('No unallocated balance available on this payment voucher.');
            }

            $allocTotal = 0.00;
            foreach ($allocations as $alloc) {
                $allocTotal += round((float) ($alloc['amount'] ?? 0), 2);
            }

            if ($allocTotal > $availableUnallocated) {
                throw new InvalidArgumentException("Allocation request ({$allocTotal}) exceeds unallocated balance ({$availableUnallocated}).");
            }

            foreach ($allocations as $allocItem) {
                $amount = round((float) ($allocItem['amount'] ?? 0), 2);
                if ($amount <= 0) {
                    continue;
                }

                $purchase = Purchase::where('id', $allocItem['purchase_id'])
                    ->where('supplier_id', $lockedPayment->supplier_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($amount > (float) $purchase->due_amount) {
                    throw new Exception("Allocated amount ({$amount}) exceeds due ({$purchase->due_amount}) for bill #{$purchase->purchase_number}.");
                }

                $lockedPayment->allocations()->create([
                    'purchase_id'      => $purchase->id,
                    'allocated_amount' => $amount,
                    'remarks'          => $allocItem['remarks'] ?? 'Advance settlement allocation',
                ]);

                $newPaid = round((float) $purchase->paid_amount + $amount, 2);
                $newDue = max(0.00, round((float) $purchase->grand_total - $newPaid, 2));

                $purchase->update([
                    'paid_amount'    => $newPaid,
                    'due_amount'     => $newDue,
                    'payment_status' => ($newDue <= 0.00) ? 'paid' : 'partially_paid',
                ]);
            }

            $newAllocated = round((float) $lockedPayment->allocated_amount + $allocTotal, 2);
            $newUnallocated = max(0.00, round((float) $lockedPayment->amount - $newAllocated, 2));

            $lockedPayment->update([
                'allocated_amount'   => $newAllocated,
                'unallocated_amount' => $newUnallocated,
            ]);

            return $lockedPayment->load(['allocations.purchase']);
        });
    }

    /**
     * Cancel a payment voucher, restore invoice balances, and post a ledger reversal.
     *
     * @param SupplierPayment $payment
     * @param string|null $cancellationReason
     * @return SupplierPayment
     * @throws Exception
     */
    public function cancelPayment(SupplierPayment $payment, ?string $cancellationReason = null): SupplierPayment
    {
        return DB::transaction(function () use ($payment, $cancellationReason) {
            $lockedPayment = SupplierPayment::with('allocations')->where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status === 'cancelled') {
                throw new Exception('Payment voucher is already cancelled.');
            }

            // Reverse bill allocations
            foreach ($lockedPayment->allocations as $allocation) {
                $purchase = Purchase::where('id', $allocation->purchase_id)->lockForUpdate()->first();
                if ($purchase) {
                    $newPaid = max(0.00, round((float) $purchase->paid_amount - (float) $allocation->allocated_amount, 2));
                    $newDue = round((float) $purchase->grand_total - $newPaid, 2);

                    $status = 'unpaid';
                    if ($newPaid > 0 && $newDue > 0) {
                        $status = 'partially_paid';
                    } elseif ($newPaid >= (float) $purchase->grand_total) {
                        $status = 'paid';
                    }

                    $purchase->update([
                        'paid_amount'    => $newPaid,
                        'due_amount'     => $newDue,
                        'payment_status' => $status,
                    ]);
                }
            }

            // Post reversal credit entry back to Supplier Ledger
            $this->ledgerService->recordEntry($lockedPayment->supplier, [
                'transaction_date' => now()->toDateString(),
                'transaction_type' => 'payment_cancellation',
                'reference_type'   => SupplierPayment::class,
                'reference_id'     => $lockedPayment->id,
                'reference_no'     => $lockedPayment->payment_number,
                'debit'            => 0.00,
                'credit'           => (float) $lockedPayment->amount,
                'description'      => "Reversal of payment #{$lockedPayment->payment_number}: " . ($cancellationReason ?? 'Cancelled voucher'),
                'created_by'       => Auth::id(),
            ]);

            $lockedPayment->update([
                'status'             => 'cancelled',
                'allocated_amount'   => 0.00,
                'unallocated_amount' => 0.00,
                'notes'              => $lockedPayment->notes . "\n[Cancellation Note]: " . ($cancellationReason ?? 'Cancelled by admin'),
            ]);

            return $lockedPayment;
        });
    }

    /**
     * Generate unique sequential Payment Voucher number: SPAY-YYYYMMDD-XXXX
     */
    protected function generatePaymentNumber(): string
    {
        $prefix = 'SPAY-' . date('Ymd') . '-';
        $lastPayment = SupplierPayment::where('payment_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastPayment) {
            $parts = explode('-', $lastPayment->payment_number);
            $seq = intval(end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
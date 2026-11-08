<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\SupplierDebitCreditNote;
use App\Models\SupplierExchange;
use App\Models\SupplierLedgerEntry;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierLedgerService
{
    /**
     * Record an immutable ledger entry with strict concurrency locking.
     * Supplier Account Convention:
     * - Credit (+) increases liability (Amounts owed to supplier, e.g., Purchase Bills).
     * - Debit (-) decreases liability (Payments made, Returns, Supplier Debit Notes).
     * - Positive balance indicates Net Payable; Negative balance indicates Net Advance.
     *
     * @param Supplier $supplier
     * @param array $entryData [
     *     'transaction_date' => string|Carbon,
     *     'transaction_type' => string,
     *     'reference_type'   => string|null,
     *     'reference_id'     => int|null,
     *     'reference_no'     => string|null,
     *     'debit'            => float,
     *     'credit'           => float,
     *     'description'      => string|null,
     * ]
     * @return SupplierLedgerEntry
     * @throws Exception
     */
    public function recordEntry(Supplier $supplier, array $entryData): SupplierLedgerEntry
    {
        return DB::transaction(function () use ($supplier, $entryData) {
            // Lock supplier row to prevent race conditions during concurrent transactions
            $lockedSupplier = Supplier::where('id', $supplier->id)->lockForUpdate()->firstOrFail();

            // Fetch last entry balance with pessimistic lock
            $lastEntry = SupplierLedgerEntry::where('supplier_id', $lockedSupplier->id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $previousBalance = $lastEntry ? (float) $lastEntry->balance : 0.00;

            $debit = round((float) ($entryData['debit'] ?? 0), 2);
            $credit = round((float) ($entryData['credit'] ?? 0), 2);

            // Calculate new running balance: Previous + Credit (Payable increased) - Debit (Payable reduced)
            $newBalance = round($previousBalance + $credit - $debit, 2);

            $entryNumber = $this->generateEntryNumber();

            return SupplierLedgerEntry::create([
                'entry_number'     => $entryNumber,
                'supplier_id'      => $lockedSupplier->id,
                'transaction_date' => $entryData['transaction_date'] ?? now()->toDateString(),
                'transaction_type' => $entryData['transaction_type'],
                'reference_type'   => $entryData['reference_type'] ?? null,
                'reference_id'     => $entryData['reference_id'] ?? null,
                'reference_no'     => $entryData['reference_no'] ?? null,
                'debit'            => $debit,
                'credit'           => $credit,
                'balance'          => $newBalance,
                'description'      => $entryData['description'] ?? null,
                'created_by'       => Auth::id() ?? ($entryData['created_by'] ?? null),
            ]);
        });
    }

    /**
     * Record Supplier Initial Opening Balance.
     */
    public function recordOpeningBalance(Supplier $supplier): ?SupplierLedgerEntry
    {
        $amount = (float) $supplier->opening_balance;
        if ($amount <= 0) {
            return null;
        }

        $isPayable = ($supplier->opening_balance_type === 'payable');

        return $this->recordEntry($supplier, [
            'transaction_date' => $supplier->opening_balance_date ?? $supplier->created_at->toDateString(),
            'transaction_type' => 'opening_balance',
            'reference_type'   => Supplier::class,
            'reference_id'     => $supplier->id,
            'reference_no'     => 'OB-' . str_pad($supplier->id, 5, '0', STR_PAD_LEFT),
            'debit'            => $isPayable ? 0.00 : $amount,
            'credit'           => $isPayable ? $amount : 0.00,
            'description'      => 'Initial opening balance migration',
            'created_by'       => Auth::id(),
        ]);
    }

    /**
     * Record Purchase Bill / Invoice (Increases Supplier Liability).
     */
    public function recordPurchase(Purchase $purchase): SupplierLedgerEntry
    {
        return $this->recordEntry($purchase->supplier, [
            'transaction_date' => $purchase->purchase_date,
            'transaction_type' => 'purchase_invoice',
            'reference_type'   => Purchase::class,
            'reference_id'     => $purchase->id,
            'reference_no'     => $purchase->purchase_number,
            'debit'            => 0.00,
            'credit'           => (float) $purchase->grand_total,
            'description'      => "Purchase bill #{$purchase->purchase_number}" . ($purchase->supplier_invoice_no ? " (Inv: {$purchase->supplier_invoice_no})" : ''),
            'created_by'       => $purchase->created_by ?? Auth::id(),
        ]);
    }

    /**
     * Record Supplier Payment (Decreases Supplier Liability).
     */
    public function recordPayment(SupplierPayment $payment): SupplierLedgerEntry
    {
        return $this->recordEntry($payment->supplier, [
            'transaction_date' => $payment->payment_date,
            'transaction_type' => $payment->payment_type === 'advance' ? 'advance_payment' : 'invoice_payment',
            'reference_type'   => SupplierPayment::class,
            'reference_id'     => $payment->id,
            'reference_no'     => $payment->payment_number,
            'debit'            => (float) $payment->amount,
            'credit'           => 0.00,
            'description'      => "Supplier payment voucher #{$payment->payment_number} via {$payment->payment_method}",
            'created_by'       => $payment->created_by ?? Auth::id(),
        ]);
    }

    /**
     * Record Purchase Return Note (Decreases Supplier Liability / Credit Note).
     */
    public function recordReturn(PurchaseReturn $purchaseReturn): SupplierLedgerEntry
    {
        return $this->recordEntry($purchaseReturn->supplier, [
            'transaction_date' => $purchaseReturn->return_date,
            'transaction_type' => 'purchase_return',
            'reference_type'   => PurchaseReturn::class,
            'reference_id'     => $purchaseReturn->id,
            'reference_no'     => $purchaseReturn->return_number,
            'debit'            => (float) $purchaseReturn->total_return_amount,
            'credit'           => 0.00,
            'description'      => "Purchase return debit entry #{$purchaseReturn->return_number}",
            'created_by'       => $purchaseReturn->created_by ?? Auth::id(),
        ]);
    }

    /**
     * Record Supplier Debit or Credit Note.
     */
    public function recordDebitCreditNote(SupplierDebitCreditNote $note): SupplierLedgerEntry
    {
        $isDebit = $note->isDebitNote(); // Debit note reduces payable; Credit note increases payable

        return $this->recordEntry($note->supplier, [
            'transaction_date' => $note->note_date,
            'transaction_type' => $note->note_type,
            'reference_type'   => SupplierDebitCreditNote::class,
            'reference_id'     => $note->id,
            'reference_no'     => $note->note_number,
            'debit'            => $isDebit ? (float) $note->total_amount : 0.00,
            'credit'           => $isDebit ? 0.00 : (float) $note->total_amount,
            'description'      => "Supplier " . ($isDebit ? "Debit Note" : "Credit Note") . " #{$note->note_number}: {$note->description}",
            'created_by'       => $note->created_by ?? Auth::id(),
        ]);
    }

    /**
     * Record Price Difference for Supplier Exchange.
     */
    public function recordExchangeDifference(SupplierExchange $exchange): ?SupplierLedgerEntry
    {
        $diff = (float) $exchange->price_difference;

        if (abs($diff) <= 0.00) {
            return null;
        }

        // Positive difference: Replacement value > Returned value (We owe more -> Credit)
        // Negative difference: Returned value > Replacement value (Supplier owes us -> Debit)
        $isPayableDifference = $diff > 0;
        $absoluteAmount = abs($diff);

        return $this->recordEntry($exchange->supplier, [
            'transaction_date' => $exchange->exchange_date,
            'transaction_type' => 'exchange_adjustment',
            'reference_type'   => SupplierExchange::class,
            'reference_id'     => $exchange->id,
            'reference_no'     => $exchange->exchange_number,
            'debit'            => $isPayableDifference ? 0.00 : $absoluteAmount,
            'credit'           => $isPayableDifference ? $absoluteAmount : 0.00,
            'description'      => "Exchange adjustment for #{$exchange->exchange_number} (Diff: {$diff})",
            'created_by'       => $exchange->created_by ?? Auth::id(),
        ]);
    }

    /**
     * Get Current Real-Time Running Balance for a Supplier.
     */
    public function getCurrentBalance(int $supplierId): float
    {
        $latest = SupplierLedgerEntry::where('supplier_id', $supplierId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $latest ? (float) $latest->balance : 0.00;
    }

    /**
     * Generate unique sequential entry number: SLE-YYYYMMDD-XXXX
     */
    protected function generateEntryNumber(): string
    {
        $datePrefix = 'SLE-' . date('Ymd') . '-';
        $lastEntry = SupplierLedgerEntry::where('entry_number', 'LIKE', "{$datePrefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastEntry) {
            $parts = explode('-', $lastEntry->entry_number);
            $sequence = intval(end($parts)) + 1;
        }

        return $datePrefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
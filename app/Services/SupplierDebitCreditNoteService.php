<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierDebitCreditNote;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SupplierDebitCreditNoteService
{
    protected SupplierLedgerService $ledgerService;

    public function __construct(SupplierLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Create a Supplier Debit or Credit Note for discounts, price discrepancies, or damage claims.
     *
     * @param array $data [
     *     'supplier_id', 'purchase_id', 'purchase_return_id', 'supplier_exchange_id',
     *     'note_type', // 'debit_note' or 'credit_note'
     *     'reason_type', // 'price_difference', 'post_purchase_discount', 'damaged_goods', 'tax_adjustment', etc.
     *     'note_date', 'amount', 'tax_amount', 'description', 'attachment_file', 'auto_approve'
     * ]
     * @return SupplierDebitCreditNote
     * @throws Exception
     */
    public function createNote(array $data): SupplierDebitCreditNote
    {
        return DB::transaction(function () use ($data) {
            $noteType = $data['note_type'] ?? null;
            if (!in_array($noteType, ['debit_note', 'credit_note'])) {
                throw new InvalidArgumentException("Invalid note type [{$noteType}]. Allowed types are 'debit_note' or 'credit_note'.");
            }

            $amount = round((float) ($data['amount'] ?? 0), 2);
            $taxAmount = round((float) ($data['tax_amount'] ?? 0), 2);

            if ($amount <= 0) {
                throw new InvalidArgumentException('Note base amount must be greater than zero.');
            }

            $totalAmount = round($amount + $taxAmount, 2);
            $autoApprove = $data['auto_approve'] ?? true;
            $authUserId = Auth::id();

            $note = SupplierDebitCreditNote::create([
                'note_number'          => $this->generateNoteNumber($noteType),
                'supplier_id'          => $data['supplier_id'],
                'purchase_id'          => $data['purchase_id'] ?? null,
                'purchase_return_id'   => $data['purchase_return_id'] ?? null,
                'supplier_exchange_id' => $data['supplier_exchange_id'] ?? null,
                'note_type'            => $noteType,
                'reason_type'          => $data['reason_type'] ?? 'general_adjustment',
                'note_date'            => $data['note_date'] ?? now()->toDateString(),
                'amount'               => $amount,
                'tax_amount'           => $taxAmount,
                'total_amount'         => $totalAmount,
                'status'               => $autoApprove ? 'approved' : 'draft',
                'attachment_file'      => $data['attachment_file'] ?? null,
                'created_by'           => $authUserId ?? ($data['created_by'] ?? null),
                'approved_by'          => $autoApprove ? ($authUserId ?? ($data['approved_by'] ?? null)) : null,
                'description'          => $data['description'] ?? null,
            ]);

            if ($autoApprove) {
                $this->applyFinancialImpact($note);
            }

            return $note->load(['supplier', 'purchase']);
        });
    }

    /**
     * Approve a drafted debit/credit note and synchronize with ledger and bill balances.
     *
     * @param SupplierDebitCreditNote $note
     * @param int|null $approvedBy
     * @return SupplierDebitCreditNote
     * @throws Exception
     */
    public function approveNote(SupplierDebitCreditNote $note, ?int $approvedBy = null): SupplierDebitCreditNote
    {
        return DB::transaction(function () use ($note, $approvedBy) {
            $lockedNote = SupplierDebitCreditNote::where('id', $note->id)->lockForUpdate()->firstOrFail();

            if ($lockedNote->status === 'approved') {
                throw new Exception('Note is already approved.');
            }

            $lockedNote->update([
                'status'      => 'approved',
                'approved_by' => $approvedBy ?? Auth::id(),
            ]);

            $this->applyFinancialImpact($lockedNote);

            return $lockedNote;
        });
    }

    /**
     * Cancel an active note, restore ledger balance, and reverse invoice adjustments.
     *
     * @param SupplierDebitCreditNote $note
     * @param string|null $cancellationReason
     * @return SupplierDebitCreditNote
     * @throws Exception
     */
    public function cancelNote(SupplierDebitCreditNote $note, ?string $cancellationReason = null): SupplierDebitCreditNote
    {
        return DB::transaction(function () use ($note, $cancellationReason) {
            $lockedNote = SupplierDebitCreditNote::where('id', $note->id)->lockForUpdate()->firstOrFail();

            if ($lockedNote->status === 'cancelled') {
                throw new Exception('Note is already cancelled.');
            }

            $wasApproved = ($lockedNote->status === 'approved');

            $lockedNote->update([
                'status'      => 'cancelled',
                'description' => $lockedNote->description . "\n[Cancellation Reason]: " . ($cancellationReason ?? 'Cancelled by admin'),
            ]);

            if ($wasApproved) {
                // Post countervailing entry to Supplier Ledger
                $isDebit = $lockedNote->isDebitNote();
                $reversalType = $isDebit ? 'debit_note_reversal' : 'credit_note_reversal';

                $this->ledgerService->recordEntry($lockedNote->supplier, [
                    'transaction_date' => now()->toDateString(),
                    'transaction_type' => $reversalType,
                    'reference_type'   => SupplierDebitCreditNote::class,
                    'reference_id'     => $lockedNote->id,
                    'reference_no'     => $lockedNote->note_number,
                    'debit'            => $isDebit ? 0.00 : (float) $lockedNote->total_amount,
                    'credit'           => $isDebit ? (float) $lockedNote->total_amount : 0.00,
                    'description'      => "Reversal of {$lockedNote->note_type} #{$lockedNote->note_number}",
                    'created_by'       => Auth::id(),
                ]);

                // Reverse purchase bill due adjustment if linked
                if ($lockedNote->purchase_id) {
                    $purchase = Purchase::where('id', $lockedNote->purchase_id)->lockForUpdate()->first();
                    if ($purchase) {
                        $noteTotal = (float) $lockedNote->total_amount;
                        if ($isDebit) {
                            // Previously reduced due, so restore due amount
                            $purchase->due_amount = round((float) $purchase->due_amount + $noteTotal, 2);
                        } else {
                            // Previously increased liability/due, so decrease due amount
                            $purchase->due_amount = max(0.00, round((float) $purchase->due_amount - $noteTotal, 2));
                        }

                        $purchase->payment_status = ($purchase->due_amount <= 0.00) ? 'paid' : 'partially_paid';
                        $purchase->save();
                    }
                }
            }

            return $lockedNote;
        });
    }

    /**
     * Apply ledger entry and purchase invoice adjustments when note is approved.
     */
    protected function applyFinancialImpact(SupplierDebitCreditNote $note): void
    {
        // 1. Post to Supplier Ledger
        $this->ledgerService->recordDebitCreditNote($note);

        // 2. Adjust linked purchase bill due balance if applicable
        if (!empty($note->purchase_id)) {
            $purchase = Purchase::where('id', $note->purchase_id)->lockForUpdate()->first();
            if ($purchase) {
                $noteTotal = (float) $note->total_amount;

                if ($note->isDebitNote()) {
                    // Debit note reduces amount owed to supplier
                    $newDue = max(0.00, round((float) $purchase->due_amount - $noteTotal, 2));
                    $purchase->due_amount = $newDue;
                    if ($newDue <= 0.00) {
                        $purchase->payment_status = 'paid';
                    }
                } else {
                    // Credit note increases amount owed to supplier
                    $purchase->due_amount = round((float) $purchase->due_amount + $noteTotal, 2);
                    $purchase->payment_status = 'partially_paid';
                }

                $purchase->save();
            }
        }
    }

    /**
     * Generate unique sequential note number: SDN-YYYYMMDD-XXXX or SCN-YYYYMMDD-XXXX
     */
    protected function generateNoteNumber(string $noteType): string
    {
        $prefix = ($noteType === 'debit_note' ? 'SDN-' : 'SCN-') . date('Ymd') . '-';

        $lastNote = SupplierDebitCreditNote::where('note_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastNote) {
            $parts = explode('-', $lastNote->note_number);
            $seq = intval(end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
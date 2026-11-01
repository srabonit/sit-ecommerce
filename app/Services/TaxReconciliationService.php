<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class TaxReconciliationService
{
    /**
     * Calculate monthly or custom date range VAT reconciliation summary
     *
     * @param string $startDate Y-m-d
     * @param string $endDate   Y-m-d
     * @param int|null $branchId
     * @return array
     */
    public static function getPeriodReconciliation(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        // 1. Output VAT from Sales (Completed & Delivered Orders)
        $salesQuery = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotIn('order_status', ['cancelled', 'failed', 'incomplete']);

        if ($branchId && Schema::hasColumn('orders', 'branch_id')) {
            $salesQuery->where('branch_id', $branchId);
        }

        $grossSales = (float) (clone $salesQuery)->sum('grand_total');

        // Schema-Safe Dynamic Checks
        $hasTaxableBase = Schema::hasColumn('orders', 'taxable_base_amount');
        $hasTaxAmount   = Schema::hasColumn('orders', 'tax_amount');
        $hasProductTax  = Schema::hasColumn('orders', 'product_tax_amount');
        $hasShippingTax = Schema::hasColumn('orders', 'shipping_tax_amount');

        $taxableBaseExpr = $hasTaxableBase ? "COALESCE(taxable_base_amount, subtotal)" : "subtotal";
        $taxableBase = (float) (clone $salesQuery)->sum(DB::raw($taxableBaseExpr));

        $outputVatExpr = $hasTaxAmount ? "COALESCE(tax_amount, 0)" : "0";
        $outputVat = (float) (clone $salesQuery)->sum(DB::raw($outputVatExpr));

        $productVatExpr = $hasProductTax ? "COALESCE(product_tax_amount, tax_amount, 0)" : ($hasTaxAmount ? "COALESCE(tax_amount, 0)" : "0");
        $productVat = (float) (clone $salesQuery)->sum(DB::raw($productVatExpr));

        $shippingVatExpr = $hasShippingTax ? "COALESCE(shipping_tax_amount, 0)" : "0";
        $shippingVat = (float) (clone $salesQuery)->sum(DB::raw($shippingVatExpr));

        $totalOrdersCount = (int) (clone $salesQuery)->count();

        // 2. VAT Adjustments (Returns, Cancellations, Reversals)
        $adjustedTaxReversed = 0.00;
        if (Schema::hasTable('tax_adjustments')) {
            $adjQuery = DB::table('tax_adjustments')
                ->whereBetween('created_at', [$start, $end]);

            if ($branchId) {
                $adjQuery->whereIn('order_id', function ($sq) use ($branchId) {
                    $sq->select('id')->from('orders')->where('branch_id', $branchId);
                });
            }

            $adjustedTaxReversed = (float) $adjQuery->sum('net_tax_change');
        }

        // Net Output VAT Liability after return reversals
        $netOutputVat = max(0.00, round($outputVat - $adjustedTaxReversed, 2));

        // 3. Treasury Payments Deposited
        $treasuryPaid = 0.00;
        $pendingPayments = 0.00;
        if (Schema::hasTable('tax_payments')) {
            $payQuery = DB::table('tax_payments')
                ->whereBetween('payment_date', [$startDate, $endDate]);

            $treasuryPaid = (float) (clone $payQuery)->where('payment_status', 'completed')->sum('amount');
            $pendingPayments = (float) (clone $payQuery)->where('payment_status', 'pending')->sum('amount');
        }

        // 4. Net VAT Payable / (Advance Treasury Balance)
        $netTaxPayable = round($netOutputVat - $treasuryPaid, 2);

        return [
            'period' => [
                'start_date' => $start->toDateString(),
                'end_date'   => $end->toDateString(),
                'month_name' => $start->format('F Y'),
            ],
            'sales' => [
                'total_orders'  => $totalOrdersCount,
                'gross_sales'   => round($grossSales, 2),
                'taxable_base'  => round($taxableBase, 2),
                'effective_rate'=> ($taxableBase > 0) ? round(($outputVat / $taxableBase) * 100, 2) : 0.00,
            ],
            'output_vat' => [
                'gross_vat'    => round($outputVat, 2),
                'product_vat'  => round($productVat, 2),
                'shipping_vat' => round($shippingVat, 2),
                'reversed_vat' => round($adjustedTaxReversed, 2),
                'net_vat'      => $netOutputVat,
            ],
            'treasury' => [
                'deposited'    => round($treasuryPaid, 2),
                'pending'      => round($pendingPayments, 2),
                'balance_due'  => max(0.00, $netTaxPayable),
                'advance_paid' => $netTaxPayable < 0 ? abs($netTaxPayable) : 0.00,
            ],
        ];
    }

    /**
     * Check if a specific date or period is locked against modifications
     *
     * @param string|Carbon $date
     * @return bool
     */
    public static function isDateLocked($date): bool
    {
        if (!Schema::hasTable('tax_periods')) {
            return false;
        }

        $checkDate = Carbon::parse($date)->toDateString();

        return DB::table('tax_periods')
            ->where('is_locked', true)
            ->where('start_date', '<=', $checkDate)
            ->where('end_date', '>=', $checkDate)
            ->exists();
    }

    /**
     * Close and lock a monthly tax period with immutable snapshot
     *
     * @param string $periodKey "2026-09"
     * @param int|null $adminId
     * @param string|null $remarks
     * @return array
     */
    public static function closePeriod(string $periodKey, ?int $adminId = null, ?string $remarks = null): array
    {
        if (!Schema::hasTable('tax_periods')) {
            return ['success' => false, 'message' => 'tax_periods table does not exist.'];
        }

        $periodDate = Carbon::parse($periodKey . '-01');
        $startDate = $periodDate->copy()->startOfMonth()->toDateString();
        $endDate   = $periodDate->copy()->endOfMonth()->toDateString();

        $existing = DB::table('tax_periods')->where('period_key', $periodKey)->first();
        if ($existing && $existing->is_locked) {
            return ['success' => false, 'message' => "Tax period {$periodKey} is already locked."];
        }

        $reconciliation = self::getPeriodReconciliation($startDate, $endDate);

        $periodData = [
            'period_key'          => $periodKey,
            'period_name'         => $periodDate->format('F Y'),
            'start_date'          => $startDate,
            'end_date'            => $endDate,
            'taxable_amount'      => $reconciliation['sales']['taxable_base'],
            'tax_collected'       => $reconciliation['output_vat']['gross_vat'],
            'tax_refunded'        => $reconciliation['output_vat']['reversed_vat'],
            'net_tax_liability'   => $reconciliation['output_vat']['net_vat'],
            'treasury_paid'       => $reconciliation['treasury']['deposited'],
            'is_locked'           => true,
            'locked_at'           => now(),
            'locked_by'           => $adminId,
            'closing_snapshot'    => json_encode($reconciliation),
            'remarks'             => $remarks,
            'updated_at'          => now(),
        ];

        if ($existing) {
            DB::table('tax_periods')->where('id', $existing->id)->update($periodData);
            $periodId = $existing->id;
        } else {
            $periodData['created_at'] = now();
            $periodId = DB::table('tax_periods')->insertGetId($periodData);
        }

        Log::info("Tax period {$periodKey} locked successfully by Admin #{$adminId}", $reconciliation);

        return [
            'success'   => true,
            'period_id' => $periodId,
            'period'    => $periodKey,
            'summary'   => $reconciliation,
        ];
    }
}
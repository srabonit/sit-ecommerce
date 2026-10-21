<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\InventoryService;

class SyncInventoryStocksAndBackfillCogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:sync-and-backfill {--dry-run : Inspect what would change without modifying the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync parent product stocks with variants sum and backfill historic COGS/purchase_cost for past order items.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->newLine();
        $this->info("==================================================================");
        $this->info("  INVENTORY DATA AUDIT & HISTORIC COGS BACKFILL ENGINE            ");
        $this->info("  Mode: " . ($isDryRun ? "DRY RUN (Preview Only)" : "LIVE DATABASE UPDATE"));
        $this->info("==================================================================");
        $this->newLine();

        // =========================================================================
        // PART 1: Variable Products Parent Stock Synchronization
        // =========================================================================
        $this->comment("STEP 1: Checking Variable Products for Stock Discrepancies...");

        if (Schema::hasTable('product_variants')) {
            $variableProductIds = DB::table('product_variants')
                ->distinct()
                ->pluck('product_id');

            $desyncedProducts = [];

            foreach ($variableProductIds as $pId) {
                $actualSum = (int) DB::table('product_variants')
                    ->where('product_id', $pId)
                    ->sum('stock');

                $currentParentStock = (int) (DB::table('products')->where('id', $pId)->value('stock') ?? 0);

                if ($actualSum !== $currentParentStock) {
                    $desyncedProducts[] = [
                        'product_id'    => $pId,
                        'current_stock' => $currentParentStock,
                        'variant_sum'   => $actualSum,
                        'diff'          => $actualSum - $currentParentStock,
                    ];
                }
            }

            if (count($desyncedProducts) > 0) {
                $this->warn("Found " . count($desyncedProducts) . " variable products with desynced parent stock.");
                $this->table(['Product ID', 'Current Parent Stock', 'Actual Variants Sum', 'Difference'], $desyncedProducts);

                if (!$isDryRun) {
                    $syncedCount = 0;
                    foreach ($desyncedProducts as $row) {
                        InventoryService::syncParentProductStock((int)$row['product_id']);
                        $syncedCount++;
                    }
                    $this->info("✓ Successfully synced {$syncedCount} parent product stocks.");
                } else {
                    $this->line(" [Dry Run] Skipped syncing parent stocks.");
                }
            } else {
                $this->info("✓ All variable products are already 100% in sync with variants.");
            }
        } else {
            $this->warn("product_variants table not found. Skipping Step 1.");
        }

        $this->newLine();

        // =========================================================================
        // PART 2: Historic Order Items COGS & Purchase Cost Backfill
        // =========================================================================
        $this->comment("STEP 2: Inspecting Historic Order Items without Purchase Cost...");

        if (Schema::hasTable('order_items')) {
            $qtyCol = Schema::hasColumn('order_items', 'quantity') ? 'quantity' : (Schema::hasColumn('order_items', 'qty') ? 'qty' : null);

            $missingCostQuery = DB::table('order_items')
                ->where(function ($q) {
                    $q->whereNull('purchase_cost')
                      ->orWhere('purchase_cost', '<=', 0);
                });

            $missingCount = $missingCostQuery->count();

            if ($missingCount > 0) {
                $this->warn("Found {$missingCount} order items with missing purchase cost / COGS.");

                if (!$isDryRun) {
                    $updatedItems = 0;
                    $items = $missingCostQuery->get();

                    $this->output->progressStart(count($items));

                    foreach ($items as $item) {
                        $qty = $qtyCol ? (int)($item->$qtyCol ?? 1) : 1;
                        $resolvedCost = 0.00;
                        $isEstimated = false;

                        // 1. Try resolving from variant
                        if (!empty($item->variant_id) && Schema::hasTable('product_variants')) {
                            $v = DB::table('product_variants')->where('id', $item->variant_id)->first();
                            if ($v) {
                                $vCost = (float)($v->purchase_cost ?? ($v->purchase_price ?? 0));
                                if ($vCost > 0) {
                                    $resolvedCost = $vCost;
                                }
                            }
                        }

                        // 2. Fallback to product cost
                        if ($resolvedCost <= 0 && !empty($item->product_id) && Schema::hasTable('products')) {
                            $p = DB::table('products')->where('id', $item->product_id)->first();
                            if ($p) {
                                $pCost = (float)($p->purchase_cost ?? ($p->purchase_price ?? 0));
                                if ($pCost > 0) {
                                    $resolvedCost = $pCost;
                                    $isEstimated = true;
                                }
                            }
                        }

                        if ($resolvedCost <= 0) {
                            $isEstimated = true;
                        }

                        $cogsVal = round($resolvedCost * $qty, 2);

                        $updatePayload = [
                            'purchase_cost'     => round($resolvedCost, 2),
                            'cogs'              => $cogsVal,
                            'is_cost_estimated' => $isEstimated,
                        ];

                        DB::table('order_items')->where('id', $item->id)->update($updatePayload);
                        $updatedItems++;

                        $this->output->progressAdvance();
                    }

                    $this->output->progressFinish();
                    $this->info("✓ Successfully backfilled {$updatedItems} order items with COGS snapshots.");
                } else {
                    $this->line(" [Dry Run] Skipped updating {$missingCount} order items.");
                }
            } else {
                $this->info("✓ All order items already possess valid purchase cost and COGS records.");
            }
        } else {
            $this->warn("order_items table not found. Skipping Step 2.");
        }

        $this->newLine();

        // =========================================================================
        // PART 3: Current Inventory Valuation Summary
        // =========================================================================
        $this->comment("STEP 3: Real-Time Valuation Summary (No Double-Counting):");

        $valuation = InventoryService::getInventoryValuation();

        $this->table(
            ['Metric', 'Total Units', 'Valuation (BDT)'],
            [
                ['Simple Products', number_format($valuation['simple_units']), '৳ ' . number_format($valuation['simple_valuation'], 2)],
                ['Variable Products (Variants)', number_format($valuation['variant_units']), '৳ ' . number_format($valuation['variant_valuation'], 2)],
                ['TOTAL INVENTORY', number_format($valuation['total_units']), '৳ ' . number_format($valuation['total_valuation'], 2)],
            ]
        );

        $this->newLine();
        $this->info("Audit and synchronization routine completed successfully.");
        return Command::SUCCESS;
    }
}
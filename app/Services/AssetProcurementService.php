<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetProcurementService
{
    /**
     * Register a capital asset procured through a verified Purchase Invoice.
     *
     * @param Purchase $purchase
     * @param array $assetData [
     *     'name', 'category_id', 'brand_name', 'model_number', 'serial_number',
     *     'branch_id', 'department', 'physical_location', 'assigned_user_id',
     *     'purchase_cost', 'salvage_value', 'depreciation_method', 'depreciation_rate',
     *     'warranty_expiry_date', 'warranty_document', 'notes', 'condition', 'status'
     * ]
     * @return Asset
     * @throws Exception
     */
    public function registerAssetFromPurchase(Purchase $purchase, array $assetData): Asset
    {
        return DB::transaction(function () use ($purchase, $assetData) {
            if (empty($assetData['name'])) {
                throw new InvalidArgumentException('Asset name is required.');
            }

            $purchaseCost = round((float) ($assetData['purchase_cost'] ?? 0), 2);
            if ($purchaseCost <= 0) {
                throw new InvalidArgumentException('Purchase cost for the asset must be greater than zero.');
            }

            $salvageValue = round((float) ($assetData['salvage_value'] ?? 0.00), 2);
            $depreciationRate = round((float) ($assetData['depreciation_rate'] ?? 0.00), 2);

            $asset = Asset::create([
                'asset_code'               => $this->generateAssetCode(),
                'name'                     => $assetData['name'],
                'category_id'              => $assetData['category_id'] ?? null,
                'brand_name'               => $assetData['brand_name'] ?? null,
                'model_number'             => $assetData['model_number'] ?? null,
                'serial_number'            => $assetData['serial_number'] ?? null,
                'supplier_id'              => $purchase->supplier_id,
                'purchase_id'              => $purchase->id,
                'purchase_date'            => $purchase->purchase_date,
                'purchase_cost'            => $purchaseCost,
                'branch_id'                => $assetData['branch_id'] ?? $purchase->branch_id,
                'department'               => $assetData['department'] ?? null,
                'physical_location'        => $assetData['physical_location'] ?? null,
                'assigned_user_id'         => $assetData['assigned_user_id'] ?? null,
                'condition'                => $assetData['condition'] ?? 'new',
                'status'                   => $assetData['status'] ?? 'in_use',
                'current_value'            => $purchaseCost,
                'salvage_value'            => $salvageValue,
                'depreciation_method'      => $assetData['depreciation_method'] ?? 'straight_line',
                'depreciation_rate'        => $depreciationRate,
                'accumulated_depreciation' => 0.00,
                'warranty_expiry_date'     => $assetData['warranty_expiry_date'] ?? null,
                'warranty_document'        => $assetData['warranty_document'] ?? null,
                'notes'                    => $assetData['notes'] ?? "Procured via Purchase Bill #{$purchase->purchase_number}",
            ]);

            return $asset->load(['supplier', 'purchase', 'branch', 'assignedUser']);
        });
    }

    /**
     * Direct Asset Acquisition Registration (Standalone or gift/legacy assets).
     *
     * @param array $data
     * @return Asset
     * @throws Exception
     */
    public function registerDirectAsset(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['name'])) {
                throw new InvalidArgumentException('Asset name is required.');
            }

            $purchaseCost = round((float) ($data['purchase_cost'] ?? 0), 2);
            $salvageValue = round((float) ($data['salvage_value'] ?? 0.00), 2);
            $depreciationRate = round((float) ($data['depreciation_rate'] ?? 0.00), 2);
            $initialAccumulated = round((float) ($data['accumulated_depreciation'] ?? 0.00), 2);
            $currentValue = max(0.00, round($purchaseCost - $initialAccumulated, 2));

            return Asset::create([
                'asset_code'               => $this->generateAssetCode(),
                'name'                     => $data['name'],
                'category_id'              => $data['category_id'] ?? null,
                'brand_name'               => $data['brand_name'] ?? null,
                'model_number'             => $data['model_number'] ?? null,
                'serial_number'            => $data['serial_number'] ?? null,
                'supplier_id'              => $data['supplier_id'] ?? null,
                'purchase_id'              => null,
                'purchase_date'            => $data['purchase_date'] ?? now()->toDateString(),
                'purchase_cost'            => $purchaseCost,
                'branch_id'                => $data['branch_id'] ?? null,
                'department'               => $data['department'] ?? null,
                'physical_location'        => $data['physical_location'] ?? null,
                'assigned_user_id'         => $data['assigned_user_id'] ?? null,
                'condition'                => $data['condition'] ?? 'good',
                'status'                   => $data['status'] ?? 'in_use',
                'current_value'            => $currentValue,
                'salvage_value'            => $salvageValue,
                'depreciation_method'      => $data['depreciation_method'] ?? 'straight_line',
                'depreciation_rate'        => $depreciationRate,
                'accumulated_depreciation' => $initialAccumulated,
                'warranty_expiry_date'     => $data['warranty_expiry_date'] ?? null,
                'warranty_document'        => $data['warranty_document'] ?? null,
                'notes'                    => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Transfer or reassign asset to another user, department, or branch.
     *
     * @param Asset $asset
     * @param array $transferData ['assigned_user_id', 'branch_id', 'department', 'physical_location', 'notes']
     * @return Asset
     * @throws Exception
     */
    public function transferAsset(Asset $asset, array $transferData): Asset
    {
        return DB::transaction(function () use ($asset, $transferData) {
            $lockedAsset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            $transferNote = sprintf(
                "\n[Transfer on %s]: Reassigned from Branch %s/User %s to Branch %s/User %s",
                now()->toDateTimeString(),
                $lockedAsset->branch_id ?? 'None',
                $lockedAsset->assigned_user_id ?? 'Unassigned',
                $transferData['branch_id'] ?? $lockedAsset->branch_id,
                $transferData['assigned_user_id'] ?? 'Unassigned'
            );

            $lockedAsset->update([
                'assigned_user_id'  => $transferData['assigned_user_id'] ?? $lockedAsset->assigned_user_id,
                'branch_id'         => $transferData['branch_id'] ?? $lockedAsset->branch_id,
                'department'        => $transferData['department'] ?? $lockedAsset->department,
                'physical_location' => $transferData['physical_location'] ?? $lockedAsset->physical_location,
                'notes'             => ($lockedAsset->notes ?? '') . $transferNote,
            ]);

            return $lockedAsset;
        });
    }

    /**
     * Apply scheduled depreciation to an asset.
     *
     * @param Asset $asset
     * @param float|null $customDepreciationAmount
     * @return Asset
     * @throws Exception
     */
    public function applyDepreciation(Asset $asset, ?float $customDepreciationAmount = null): Asset
    {
        return DB::transaction(function () use ($asset, $customDepreciationAmount) {
            $lockedAsset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            if ($lockedAsset->status === 'disposed' || (float) $lockedAsset->current_value <= (float) $lockedAsset->salvage_value) {
                throw new Exception('Asset is already disposed or has reached its salvage threshold.');
            }

            $depreciation = 0.00;
            if ($customDepreciationAmount !== null) {
                $depreciation = round($customDepreciationAmount, 2);
            } else {
                $rate = (float) $lockedAsset->depreciation_rate;
                if ($rate <= 0) {
                    throw new Exception('Depreciation rate is not defined for this asset.');
                }

                if ($lockedAsset->depreciation_method === 'straight_line') {
                    $depreciableBase = max(0.00, (float) $lockedAsset->purchase_cost - (float) $lockedAsset->salvage_value);
                    $depreciation = round($depreciableBase * ($rate / 100), 2);
                } else {
                    // Declining balance method
                    $depreciation = round((float) $lockedAsset->current_value * ($rate / 100), 2);
                }
            }

            $maxAllowedDepreciation = max(0.00, (float) $lockedAsset->current_value - (float) $lockedAsset->salvage_value);
            $effectiveDepreciation = min($depreciation, $maxAllowedDepreciation);

            $newAccumulated = round((float) $lockedAsset->accumulated_depreciation + $effectiveDepreciation, 2);
            $newCurrentValue = max((float) $lockedAsset->salvage_value, round((float) $lockedAsset->purchase_cost - $newAccumulated, 2));

            $lockedAsset->update([
                'accumulated_depreciation' => $newAccumulated,
                'current_value'            => $newCurrentValue,
                'notes'                    => ($lockedAsset->notes ?? '') . sprintf("\n[Depreciation %s]: -%0.2f", now()->toDateString(), $effectiveDepreciation),
            ]);

            return $lockedAsset;
        });
    }

    /**
     * Generate unique sequential asset code: AST-YYYY-XXXX
     */
    protected function generateAssetCode(): string
    {
        $yearPrefix = 'AST-' . date('Y') . '-';
        $lastAsset = Asset::where('asset_code', 'LIKE', "{$yearPrefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastAsset) {
            $parts = explode('-', $lastAsset->asset_code);
            $seq = intval(end($parts)) + 1;
        }

        return $yearPrefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
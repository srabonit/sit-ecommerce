<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;

class TaxSettingController extends Controller
{
    /**
     * Managed tax configuration keys.
     * Hierarchy Reference:
     * 1. Global Switch (vat_enabled) -> If OFF, all calculations are 0.00
     * 2. Branch Exemption / Zero-Rate Rule
     * 3. Product-Specific Direct VAT (0%, 2%, Flat, etc. strictly honored)
     * 4. Category-Specific Direct VAT (Subcategory > Category)
     * 5. Tax Rules Database Cascade
     * 6. Central Settings Default VAT (default_vat_rate - 5%)
     */
    protected array $taxSettingKeys = [
        'vat_enabled',
        'enable_vat',
        'vat_label',
        'default_vat_rate',
        'default_tax_calculation_type',
        'apply_vat_on_shipping',
        'company_bin',
        'bin_number',
        'vat_registration_name',
        'vat_circle_or_division',
        'enable_pos_vat',
        'pos_vat_display_mode',
        'display_vat_breakdown_on_checkout',
        'enable_round_off_vat',
        'auto_lock_tax_periods',
    ];

    /**
     * Display central tax & VAT configuration interface
     */
    public function index()
    {
        $rawSettings = [];
        if (Schema::hasTable('settings')) {
            $rawSettings = DB::table('settings')
                ->whereIn('key', $this->taxSettingKeys)
                ->pluck('value', 'key')
                ->toArray();
        }

        // Resolves whether VAT is enabled (checks both vat_enabled and enable_vat)
        $isVatEnabled = false;
        if (isset($rawSettings['vat_enabled'])) {
            $isVatEnabled = filter_var($rawSettings['vat_enabled'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($rawSettings['enable_vat'])) {
            $isVatEnabled = filter_var($rawSettings['enable_vat'], FILTER_VALIDATE_BOOLEAN);
        }

        $bin = $rawSettings['company_bin'] ?? ($rawSettings['bin_number'] ?? '001293847-0101');

        $settings = [
            'vat_enabled'                        => $isVatEnabled,
            'vat_label'                          => $rawSettings['vat_label'] ?? 'VAT',
            'default_vat_rate'                   => isset($rawSettings['default_vat_rate']) ? (float)$rawSettings['default_vat_rate'] : 5.00,
            'default_tax_calculation_type'       => $rawSettings['default_tax_calculation_type'] ?? 'exclusive',
            'apply_vat_on_shipping'              => filter_var($rawSettings['apply_vat_on_shipping'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'company_bin'                        => $bin,
            'vat_registration_name'              => $rawSettings['vat_registration_name'] ?? config('app.name', 'Shop Now BD'),
            'vat_circle_or_division'             => $rawSettings['vat_circle_or_division'] ?? '',
            'enable_pos_vat'                     => filter_var($rawSettings['enable_pos_vat'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'pos_vat_display_mode'               => $rawSettings['pos_vat_display_mode'] ?? 'item_breakdown',
            'display_vat_breakdown_on_checkout' => filter_var($rawSettings['display_vat_breakdown_on_checkout'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'enable_round_off_vat'               => filter_var($rawSettings['enable_round_off_vat'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'auto_lock_tax_periods'              => filter_var($rawSettings['auto_lock_tax_periods'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];

        return view('admin.tax.settings.index', compact('settings'));
    }

    /**
     * Update and persist central VAT & tax configurations
     */
    public function update(Request $request)
    {
        $request->validate([
            'vat_label'                    => 'required|string|max:50',
            'default_vat_rate'             => 'required|numeric|min:0|max:100',
            'default_tax_calculation_type' => 'required|in:inclusive,exclusive',
            'company_bin'                  => 'nullable|string|max:50',
            'vat_registration_name'        => 'nullable|string|max:150',
            'vat_circle_or_division'       => 'nullable|string|max:100',
            'pos_vat_display_mode'         => 'required|in:item_breakdown,summary_only,hidden',
        ]);

        if (!Schema::hasTable('settings')) {
            return back()->with('error', 'Settings table does not exist.');
        }

        $vatEnabledVal = $request->has('vat_enabled') ? '1' : '0';
        $companyBin = trim((string)$request->input('company_bin', ''));

        $payload = [
            'vat_enabled'                        => $vatEnabledVal,
            'enable_vat'                         => $vatEnabledVal, // Dual-key synchronization for universal compatibility
            'vat_label'                          => trim($request->input('vat_label', 'VAT')),
            'default_vat_rate'                   => number_format((float)$request->input('default_vat_rate', 5.00), 2, '.', ''),
            'default_tax_calculation_type'       => $request->input('default_tax_calculation_type', 'exclusive'),
            'apply_vat_on_shipping'              => $request->has('apply_vat_on_shipping') ? '1' : '0',
            'company_bin'                        => $companyBin,
            'bin_number'                         => $companyBin, // Dual-key sync with BIN number
            'vat_registration_name'              => trim((string)$request->input('vat_registration_name', '')),
            'vat_circle_or_division'             => trim((string)$request->input('vat_circle_or_division', '')),
            'enable_pos_vat'                     => $request->has('enable_pos_vat') ? '1' : '0',
            'pos_vat_display_mode'               => $request->input('pos_vat_display_mode', 'item_breakdown'),
            'display_vat_breakdown_on_checkout' => $request->has('display_vat_breakdown_on_checkout') ? '1' : '0',
            'enable_round_off_vat'               => $request->has('enable_round_off_vat') ? '1' : '0',
            'auto_lock_tax_periods'              => $request->has('auto_lock_tax_periods') ? '1' : '0',
        ];

        DB::beginTransaction();
        try {
            foreach ($payload as $key => $val) {
                $exists = DB::table('settings')->where('key', $key)->exists();
                if ($exists) {
                    DB::table('settings')->where('key', $key)->update([
                        'value'      => $val,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('settings')->insert([
                        'key'        => $key,
                        'value'      => $val,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Invalidate system settings cache
            Cache::forget('global_settings_cache');
            Cache::forget('tax_engine_active_rules');
            Cache::forget('central_tax_settings');

            if (class_exists(ActivityLogger::class) && method_exists(ActivityLogger::class, 'log')) {
                try {
                    ActivityLogger::log('update', 'tax_settings', 'Central VAT and Tax configurations updated', [
                        'updated_keys' => array_keys($payload),
                    ]);
                } catch (\Throwable $logEx) {
                    Log::warning('ActivityLogger notice: ' . $logEx->getMessage());
                }
            }

            return redirect()->route('admin.tax.settings.index')->with('success', 'ভ্যাট ও কর সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Tax settings save error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'সেটিংস সংরক্ষণে ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }
}
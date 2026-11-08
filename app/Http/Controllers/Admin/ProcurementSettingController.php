<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ProcurementSettingController extends Controller
{
    /**
     * Display procurement configuration form.
     */
    public function index()
    {
        $settings = [];

        // Check and fetch settings from storage table
        if (Schema::hasTable('procurement_settings')) {
            $settings = DB::table('procurement_settings')->pluck('value', 'key')->toArray();
        } elseif (Schema::hasTable('settings')) {
            $settings = DB::table('settings')->where('group', 'procurement')->pluck('value', 'key')->toArray();
        }

        // Standard ERP Fallback Defaults
        $defaultSettings = [
            'po_prefix'                    => 'PO-',
            'po_date_format'               => 'Ymd',
            'po_padding'                   => 4,
            'grn_prefix'                   => 'GRN-',
            'grn_date_format'              => 'Ymd',
            'grn_padding'                  => 4,
            'bill_prefix'                  => 'BILL-',
            'bill_date_format'             => 'Ymd',
            'bill_padding'                 => 4,
            'rtv_prefix'                   => 'RTV-',
            'rtv_date_format'              => 'Ymd',
            'rtv_padding'                  => 4,
            'payment_prefix'               => 'SPV-',
            'payment_date_format'          => 'Ymd',
            'payment_padding'              => 4,
            'exchange_prefix'              => 'EXC-',
            'exchange_date_format'         => 'Ymd',
            'exchange_padding'             => 4,
            'debit_note_prefix'            => 'SDN-',
            'debit_date_format'            => 'Ymd',
            'debit_padding'                => 4,
            'credit_note_prefix'           => 'SCN-',
            'credit_date_format'           => 'Ymd',
            'credit_padding'               => 4,
            'enforce_3way_matching'        => 1,
            'qty_variance_tolerance_pct'   => 5.0,
            'price_variance_tolerance_pct' => 0.0,
            'director_approval_threshold'  => 100000,
            'block_po_on_credit_limit'     => 1,
            'require_qc_quarantine'        => 0,
            'gl_accounts_payable_id'       => null,
            'gl_inventory_asset_id'        => null,
            'gl_purchase_expense_id'       => null,
            'gl_purchase_return_id'        => null,
            'gl_input_tax_id'              => null,
            'gl_freight_inward_id'         => null,
            'default_payment_terms_note'   => "Payment will be disbursed via BEFTN/Account Payee Cheque within agreed credit terms upon successful delivery and warehouse QC approval.",
            'default_inspection_terms_note'=> "All goods are subject to physical inspection at the receiving warehouse. Rejected lots must be replaced by the vendor within 7 business days.",
            'po_footer_disclaimer'         => "This is a computer-generated Purchase Order. Any alterations or price discrepancies must be confirmed in writing prior to shipment dispatch.",
            'signatory_left_title'         => "Prepared By (Procurement Officer)",
            'signatory_center_title'       => "Verified By (Accounts / Audit)",
            'signatory_right_title'        => "Authorized Signatory (Managing Director)",
        ];

        $settings = array_merge($defaultSettings, $settings);

        // Fetch chart of accounts for GL mapping
        $accounts = collect([]);
        if (Schema::hasTable('chart_of_accounts')) {
            $accounts = DB::table('chart_of_accounts')->select('id', 'name', 'code', 'type')->get();
        } elseif (Schema::hasTable('bank_accounts')) {
            $accounts = DB::table('bank_accounts')->select('id', 'account_name as name', 'account_number as code')->get();
        }

        return view('admin.procurement.settings.index', compact('settings', 'accounts'));
    }

    /**
     * Store or update procurement settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'po_prefix'                    => 'required|string|max:10',
            'po_date_format'               => 'required|string|in:Ymd,Ym,none',
            'po_padding'                   => 'required|integer|between:2,8',
            'grn_prefix'                   => 'required|string|max:10',
            'grn_date_format'              => 'required|string|in:Ymd,Ym,none',
            'grn_padding'                  => 'required|integer|between:2,8',
            'bill_prefix'                  => 'required|string|max:10',
            'bill_date_format'             => 'required|string|in:Ymd,Ym,none',
            'bill_padding'                 => 'required|integer|between:2,8',
            'rtv_prefix'                   => 'required|string|max:10',
            'rtv_date_format'              => 'required|string|in:Ymd,Ym,none',
            'rtv_padding'                  => 'required|integer|between:2,8',
            'payment_prefix'               => 'required|string|max:10',
            'payment_date_format'          => 'required|string|in:Ymd,Ym,none',
            'payment_padding'              => 'required|integer|between:2,8',
            'exchange_prefix'              => 'required|string|max:10',
            'exchange_date_format'         => 'required|string|in:Ymd,Ym,none',
            'exchange_padding'             => 'required|integer|between:2,8',
            'debit_note_prefix'            => 'required|string|max:10',
            'debit_date_format'            => 'required|string|in:Ymd,Ym,none',
            'debit_padding'                => 'required|integer|between:2,8',
            'credit_note_prefix'           => 'required|string|max:10',
            'credit_date_format'           => 'required|string|in:Ymd,Ym,none',
            'credit_padding'               => 'required|integer|between:2,8',
            'qty_variance_tolerance_pct'   => 'required|numeric|between:0,100',
            'price_variance_tolerance_pct' => 'required|numeric|between:0,100',
            'director_approval_threshold'  => 'required|numeric|min:0',
            'gl_accounts_payable_id'       => 'required',
            'gl_inventory_asset_id'        => 'required',
            'gl_purchase_expense_id'       => 'required',
            'gl_purchase_return_id'        => 'required',
            'gl_input_tax_id'              => 'nullable',
            'gl_freight_inward_id'         => 'nullable',
            'default_payment_terms_note'   => 'nullable|string|max:1000',
            'default_inspection_terms_note'=> 'nullable|string|max:1000',
            'po_footer_disclaimer'         => 'nullable|string|max:500',
            'signatory_left_title'         => 'nullable|string|max:100',
            'signatory_center_title'       => 'nullable|string|max:100',
            'signatory_right_title'        => 'nullable|string|max:100',
        ]);

        $validated['enforce_3way_matching']    = $request->has('enforce_3way_matching') ? 1 : 0;
        $validated['block_po_on_credit_limit'] = $request->has('block_po_on_credit_limit') ? 1 : 0;
        $validated['require_qc_quarantine']    = $request->has('require_qc_quarantine') ? 1 : 0;

        if (Schema::hasTable('procurement_settings')) {
            foreach ($validated as $key => $value) {
                DB::table('procurement_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
        } elseif (Schema::hasTable('settings')) {
            foreach ($validated as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key, 'group' => 'procurement'],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
        }

        Cache::forget('procurement_settings');

        return redirect()->back()->with('success', 'Procurement module settings and policies updated successfully.');
    }
}
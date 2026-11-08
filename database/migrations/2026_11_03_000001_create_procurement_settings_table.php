<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('procurement_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // Default ERP Procurement Configurations Seeding
        $defaultSettings = [
            // Prefix, Date Format & Padding Configurations
            ['key' => 'po_prefix', 'value' => 'PO-'],
            ['key' => 'po_date_format', 'value' => 'Ymd'],
            ['key' => 'po_padding', 'value' => '4'],

            ['key' => 'grn_prefix', 'value' => 'GRN-'],
            ['key' => 'grn_date_format', 'value' => 'Ymd'],
            ['key' => 'grn_padding', 'value' => '4'],

            ['key' => 'bill_prefix', 'value' => 'BILL-'],
            ['key' => 'bill_date_format', 'value' => 'Ymd'],
            ['key' => 'bill_padding', 'value' => '4'],

            ['key' => 'rtv_prefix', 'value' => 'RTV-'],
            ['key' => 'rtv_date_format', 'value' => 'Ymd'],
            ['key' => 'rtv_padding', 'value' => '4'],

            ['key' => 'payment_prefix', 'value' => 'SPV-'],
            ['key' => 'payment_date_format', 'value' => 'Ymd'],
            ['key' => 'payment_padding', 'value' => '4'],

            ['key' => 'exchange_prefix', 'value' => 'EXC-'],
            ['key' => 'exchange_date_format', 'value' => 'Ymd'],
            ['key' => 'exchange_padding', 'value' => '4'],

            ['key' => 'debit_note_prefix', 'value' => 'SDN-'],
            ['key' => 'debit_date_format', 'value' => 'Ymd'],
            ['key' => 'debit_padding', 'value' => '4'],

            ['key' => 'credit_note_prefix', 'value' => 'SCN-'],
            ['key' => 'credit_date_format', 'value' => 'Ymd'],
            ['key' => 'credit_padding', 'value' => '4'],

            // Tolerances & Approvals
            ['key' => 'enforce_3way_matching', 'value' => '1'],
            ['key' => 'qty_variance_tolerance_pct', 'value' => '5.0'],
            ['key' => 'price_variance_tolerance_pct', 'value' => '0.0'],
            ['key' => 'director_approval_threshold', 'value' => '100000'],
            ['key' => 'block_po_on_credit_limit', 'value' => '1'],
            ['key' => 'require_qc_quarantine', 'value' => '0'],

            // Default General Ledger Head IDs (Mapped upon chart of accounts creation)
            ['key' => 'gl_accounts_payable_id', 'value' => null],
            ['key' => 'gl_inventory_asset_id', 'value' => null],
            ['key' => 'gl_purchase_expense_id', 'value' => null],
            ['key' => 'gl_purchase_return_id', 'value' => null],
            ['key' => 'gl_input_tax_id', 'value' => null],
            ['key' => 'gl_freight_inward_id', 'value' => null],

            // Boilerplates & Standard Disclaimers
            ['key' => 'default_payment_terms_note', 'value' => 'Payment will be disbursed via BEFTN/Account Payee Cheque within agreed credit terms upon successful delivery and warehouse QC approval.'],
            ['key' => 'default_inspection_terms_note', 'value' => 'All goods are subject to physical inspection at the receiving warehouse. Rejected lots must be replaced by the vendor within 7 business days.'],
            ['key' => 'po_footer_disclaimer', 'value' => 'This is a computer-generated Purchase Order. Any alterations or price discrepancies must be confirmed in writing prior to shipment dispatch.'],
            ['key' => 'signatory_left_title', 'value' => 'Prepared By (Procurement Officer)'],
            ['key' => 'signatory_center_title', 'value' => 'Verified By (Accounts / Audit)'],
            ['key' => 'signatory_right_title', 'value' => 'Authorized Signatory (Managing Director)'],
        ];

        $now = now();
        foreach ($defaultSettings as &$setting) {
            $setting['created_at'] = $now;
            $setting['updated_at'] = $now;
        }

        DB::table('procurement_settings')->insert($defaultSettings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_settings');
    }
};
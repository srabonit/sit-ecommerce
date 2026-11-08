<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcurementNumberingService
{
    /**
     * Generate the next automated voucher number based on module configuration.
     *
     * @param string $type ('po', 'grn', 'bill', 'rtv', 'payment', 'exchange', 'debit', 'credit')
     * @return string
     */
    public static function generate(string $type): string
    {
        $settings = [];
        if (Schema::hasTable('procurement_settings')) {
            $settings = DB::table('procurement_settings')->pluck('value', 'key')->toArray();
        }

        $typeMap = [
            'po' => [
                'prefix_key' => 'po_prefix',
                'format_key' => 'po_date_format',
                'pad_key'    => 'po_padding',
                'table'      => 'purchase_orders',
                'column'     => 'po_number',
                'default'    => 'PO-',
            ],
            'grn' => [
                'prefix_key' => 'grn_prefix',
                'format_key' => 'grn_date_format',
                'pad_key'    => 'grn_padding',
                'table'      => 'purchase_receipts',
                'column'     => 'receipt_number',
                'default'    => 'GRN-',
            ],
            'bill' => [
                'prefix_key' => 'bill_prefix',
                'format_key' => 'bill_date_format',
                'pad_key'    => 'bill_padding',
                'table'      => 'purchases',
                'column'     => 'purchase_number',
                'default'    => 'BILL-',
            ],
            'rtv' => [
                'prefix_key' => 'rtv_prefix',
                'format_key' => 'rtv_date_format',
                'pad_key'    => 'rtv_padding',
                'table'      => 'purchase_returns',
                'column'     => 'return_number',
                'default'    => 'RTV-',
            ],
            'payment' => [
                'prefix_key' => 'payment_prefix',
                'format_key' => 'payment_date_format',
                'pad_key'    => 'payment_padding',
                'table'      => 'supplier_payments',
                'column'     => 'voucher_number',
                'default'    => 'SPV-',
            ],
            'exchange' => [
                'prefix_key' => 'exchange_prefix',
                'format_key' => 'exchange_date_format',
                'pad_key'    => 'exchange_padding',
                'table'      => 'supplier_exchanges',
                'column'     => 'exchange_number',
                'default'    => 'EXC-',
            ],
            'debit' => [
                'prefix_key' => 'debit_note_prefix',
                'format_key' => 'debit_date_format',
                'pad_key'    => 'debit_padding',
                'table'      => 'supplier_debit_credit_notes',
                'column'     => 'note_number',
                'default'    => 'SDN-',
            ],
            'credit' => [
                'prefix_key' => 'credit_note_prefix',
                'format_key' => 'credit_date_format',
                'pad_key'    => 'credit_padding',
                'table'      => 'supplier_debit_credit_notes',
                'column'     => 'note_number',
                'default'    => 'SCN-',
            ],
        ];

        if (!isset($typeMap[$type])) {
            return strtoupper($type) . '-' . date('Ymd') . '-' . rand(1000, 9999);
        }

        $config = $typeMap[$type];
        $prefix = $settings[$config['prefix_key']] ?? $config['default'];
        $format = $settings[$config['format_key']] ?? 'Ymd';
        $padding = (int) ($settings[$config['pad_key']] ?? 4);

        $dateString = '';
        if ($format === 'Ymd') {
            $dateString = date('Ymd') . '-';
        } elseif ($format === 'Ym') {
            $dateString = date('Ym') . '-';
        }

        $pattern = $prefix . $dateString;

        $lastSerial = 0;
        if (Schema::hasTable($config['table'])) {
            $lastRecord = DB::table($config['table'])
                ->where($config['column'], 'like', "{$pattern}%")
                ->orderBy('id', 'desc')
                ->value($config['column']);

            if ($lastRecord) {
                $extracted = substr($lastRecord, strlen($pattern));
                $lastSerial = (int) $extracted;
            }
        }

        $nextSerial = str_pad((string) ($lastSerial + 1), $padding, '0', STR_PAD_LEFT);

        return $pattern . $nextSerial;
    }
}
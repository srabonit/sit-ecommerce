<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id'      => ['required', 'integer', 'exists:suppliers,id'],
            'payment_date'     => ['required', 'date'],
            'payment_type'     => ['required', 'string', 'in:invoice_payment,bill_settlement,advance,opening_balance'],
            'payment_method'   => ['required', 'string'],
            'bank_account_id'  => ['nullable'],
            'branch_id'        => ['nullable'],
            'amount'           => ['required', 'numeric', 'gt:0'],
            'voucher_number'   => ['nullable', 'string', 'max:100'],
            'payment_number'   => ['nullable', 'string', 'max:100'],
            'reference_no'     => ['nullable', 'string', 'max:100'],
            'cheque_no'        => ['nullable', 'string', 'max:100'],
            'cheque_date'      => ['nullable', 'date'],
            'attachment_file'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'allocations'      => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $totalAmount = (float) $this->input('amount', 0);
            $allocations = $this->input('allocations', []);

            if (is_array($allocations) && count($allocations) > 0) {
                $allocatedSum = 0;
                foreach ($allocations as $key => $item) {
                    if (is_array($item)) {
                        $allocatedSum += (float) ($item['amount'] ?? 0);
                    } else {
                        $allocatedSum += (float) $item;
                    }
                }

                if (round($allocatedSum, 2) > round($totalAmount, 2)) {
                    $validator->errors()->add(
                        'allocations',
                        "The total allocated amount (৳" . number_format($allocatedSum, 2) . ") cannot exceed the payment amount (৳" . number_format($totalAmount, 2) . ")."
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'supplier_id'    => 'Supplier',
            'payment_date'   => 'Payment Date',
            'payment_type'   => 'Payment Type',
            'payment_method' => 'Payment Method',
            'bank_account_id'=> 'Bank Account',
            'amount'         => 'Payment Amount',
            'voucher_number' => 'Voucher Number',
        ];
    }
}
<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewaySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'        => 'required|string|in:bkash,nagad,upay,sslcommerz',
            'name'        => 'required|string',
            'credentials' => 'required|array',
            'is_live'     => 'required|boolean',
            'is_active'   => 'required|boolean',
        ];
    }
}
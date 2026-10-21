<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ManualPaymentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string',
            'account_number' => 'required|string',
            'instruction'    => 'nullable|string',
            'is_active'      => 'required|boolean',
        ];
    }
}
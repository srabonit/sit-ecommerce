<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class OnlineOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address'   => 'required|string',
            'payment_method'     => 'required|string', // bkash, nagad, sslcommerz, manual_bkash, cod
            'transaction_id'     => 'nullable|required_if:payment_method,manual_bkash,manual_nagad|string',
            'sender_number'      => 'nullable|string',
            'payment_screenshot' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }
}
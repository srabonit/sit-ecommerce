<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'     => 'required|exists:products,id',
            'branch_id'      => 'nullable|exists:branches,id',
            'warehouse_id'   => 'nullable|exists:warehouses,id',
            'quantity'       => 'required|integer', // Positive for add, negative for deduct
            'alert_quantity' => 'nullable|integer|min:0',
            'type'           => 'required|in:add,subtract,set',
        ];
    }
}
<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_branch_id'    => 'nullable|exists:branches,id',
            'from_warehouse_id' => 'nullable|exists:warehouses,id',
            'to_branch_id'      => 'nullable|exists:branches,id',
            'to_warehouse_id'   => 'nullable|exists:warehouses,id',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ];
    }
}
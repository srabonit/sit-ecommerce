<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Order Header Information
            'supplier_id'            => ['required', 'integer', 'exists:suppliers,id'],
            'branch_id'              => ['nullable', 'integer', 'exists:branches,id'],
            'warehouse_id'           => ['nullable', 'integer', 'exists:warehouses,id'],
            'order_date'             => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'reference_no'           => ['nullable', 'string', 'max:100'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'tax_amount'             => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'          => ['nullable', 'numeric', 'min:0'],
            'terms_conditions'       => ['nullable', 'string'],
            'notes'                  => ['nullable', 'string', 'max:1000'],

            // Line Items Validation
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id'     => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.sku'            => ['nullable', 'string', 'max:100'],
            'items.*.barcode'        => ['nullable', 'string', 'max:100'],
            'items.*.quantity'       => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost'      => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount'=> ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount'     => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Custom attribute labels for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'supplier_id'             => 'Supplier',
            'branch_id'               => 'Branch',
            'warehouse_id'            => 'Warehouse',
            'order_date'              => 'Order Date',
            'expected_delivery_date'  => 'Expected Delivery Date',
            'reference_no'            => 'Reference Number',
            'discount_amount'         => 'Discount Amount',
            'tax_amount'              => 'Tax Amount',
            'shipping_cost'           => 'Shipping Cost',
            'items'                   => 'Line Items',
            'items.*.product_id'      => 'Product',
            'items.*.variant_id'      => 'Product Variant',
            'items.*.quantity'        => 'Quantity',
            'items.*.unit_cost'       => 'Unit Cost',
            'items.*.discount_amount' => 'Item Discount',
            'items.*.tax_amount'      => 'Item Tax',
        ];
    }
}
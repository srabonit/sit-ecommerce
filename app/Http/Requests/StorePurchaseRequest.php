<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            // Header Information
            'purchase_order_id'       => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'supplier_id'             => ['required', 'integer', 'exists:suppliers,id'],
            'branch_id'               => ['nullable', 'integer', 'exists:branches,id'],
            'warehouse_id'            => ['nullable', 'integer', 'exists:warehouses,id'],
            'supplier_invoice_no'     => ['nullable', 'string', 'max:100'],
            'supplier_challan_no'     => ['nullable', 'string', 'max:100'],
            'purchase_date'           => ['required', 'date'],
            'receiving_date'          => ['nullable', 'date'],
            'purchase_type'           => ['nullable', 'in:inventory,asset,expense'],
            'purchase_status'         => ['nullable', 'in:pending,received,ordered'],

            // Financial Summary Fields
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'tax_amount'              => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'           => ['nullable', 'numeric', 'min:0'],
            'other_charges'           => ['nullable', 'numeric', 'min:0'],
            'notes'                   => ['nullable', 'string', 'max:1000'],

            // Line Items Validation
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['nullable', 'integer', 'exists:purchase_order_items,id'],
            'items.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id'      => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.sku'             => ['nullable', 'string', 'max:100'],
            'items.*.barcode'         => ['nullable', 'string', 'max:100'],
            'items.*.batch_no'        => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date'     => ['nullable', 'date'],
            'items.*.quantity'        => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost'       => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount'      => ['nullable', 'numeric', 'min:0'],
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
            'purchase_order_id'       => 'Purchase Order',
            'supplier_invoice_no'     => 'Supplier Invoice Number',
            'supplier_challan_no'     => 'Supplier Challan Number',
            'purchase_date'           => 'Purchase Date',
            'purchase_type'           => 'Purchase Type',
            'discount_amount'         => 'Order Discount',
            'tax_amount'              => 'Order Tax',
            'shipping_cost'           => 'Shipping Cost',
            'other_charges'           => 'Other Charges',
            'items'                   => 'Line Items',
            'items.*.product_id'      => 'Product',
            'items.*.variant_id'      => 'Product Variant',
            'items.*.quantity'        => 'Quantity',
            'items.*.unit_cost'       => 'Unit Cost',
            'items.*.batch_no'        => 'Batch Number',
            'items.*.expiry_date'     => 'Expiry Date',
        ];
    }
}
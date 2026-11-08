<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReceiptRequest extends FormRequest
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
            // GRN Header Information
            'purchase_order_id'          => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'purchase_id'                => ['nullable', 'integer', 'exists:purchases,id'],
            'supplier_id'                => ['required', 'integer', 'exists:suppliers,id'],
            'branch_id'                  => ['nullable', 'integer', 'exists:branches,id'],
            'warehouse_id'               => ['nullable', 'integer', 'exists:warehouses,id'],
            'supplier_challan_no'        => ['nullable', 'string', 'max:100'],
            'receiving_date'             => ['required', 'date'],
            'received_by'                => ['nullable', 'integer', 'exists:users,id'],
            'inspected_by'               => ['nullable', 'integer', 'exists:users,id'],
            'inspection_notes'           => ['nullable', 'string', 'max:1000'],
            'attachment_file'            => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Line Items Received & Quality Inspection
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['nullable', 'integer', 'exists:purchase_order_items,id'],
            'items.*.purchase_item_id'   => ['nullable', 'integer', 'exists:purchase_items,id'],
            'items.*.product_id'         => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id'         => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.sku'                => ['nullable', 'string', 'max:100'],
            'items.*.barcode'            => ['nullable', 'string', 'max:100'],
            'items.*.batch_no'           => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date'        => ['nullable', 'date'],
            'items.*.ordered_quantity'   => ['nullable', 'numeric', 'min:0'],
            'items.*.received_quantity'  => ['required', 'numeric', 'gt:0'],
            'items.*.accepted_quantity'  => ['required', 'numeric', 'min:0', 'lte:items.*.received_quantity'],
            'items.*.rejected_quantity'  => ['nullable', 'numeric', 'min:0'],
            'items.*.rejection_reason'   => ['nullable', 'required_if:items.*.rejected_quantity,gt:0', 'string', 'max:255'],
            'items.*.unit_cost'          => ['required', 'numeric', 'min:0'],
            'items.*.remarks'            => ['nullable', 'string', 'max:500'],
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
            'purchase_order_id'         => 'Purchase Order',
            'purchase_id'               => 'Purchase Bill',
            'supplier_id'               => 'Supplier',
            'branch_id'                 => 'Branch',
            'warehouse_id'              => 'Warehouse',
            'supplier_challan_no'       => 'Delivery Challan Number',
            'receiving_date'            => 'Receiving Date',
            'attachment_file'           => 'Document Attachment',
            'items'                     => 'Receipt Items',
            'items.*.product_id'        => 'Product',
            'items.*.variant_id'        => 'Product Variant',
            'items.*.received_quantity' => 'Received Quantity',
            'items.*.accepted_quantity' => 'Accepted Quantity',
            'items.*.rejected_quantity' => 'Rejected Quantity',
            'items.*.rejection_reason'  => 'Rejection Reason',
            'items.*.unit_cost'         => 'Unit Cost',
        ];
    }
}
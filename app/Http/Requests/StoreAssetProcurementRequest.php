<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetProcurementRequest extends FormRequest
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
            // Sourcing & Reference
            'purchase_id'              => ['nullable', 'integer', 'exists:purchases,id'],
            'supplier_id'              => ['nullable', 'required_without:purchase_id', 'integer', 'exists:suppliers,id'],

            // Core Asset Identification & Classification
            'name'                     => ['required', 'string', 'max:255'],
            'category_id'              => ['nullable', 'integer'],
            'brand_name'               => ['nullable', 'string', 'max:100'],
            'model_number'             => ['nullable', 'string', 'max:100'],
            'serial_number'            => ['nullable', 'string', 'max:100', 'unique:assets,serial_number'],

            // Physical Deployment & Custody
            'branch_id'                => ['nullable', 'integer', 'exists:branches,id'],
            'department'               => ['nullable', 'string', 'max:100'],
            'physical_location'        => ['nullable', 'string', 'max:255'],
            'assigned_user_id'         => ['nullable', 'integer', 'exists:users,id'],
            'condition'                => ['nullable', 'string', 'in:new,good,fair,poor,damaged'],
            'status'                   => ['nullable', 'string', 'in:in_use,in_storage,under_maintenance,disposed'],

            // Financial Capitalization & Depreciation Parameters
            'purchase_date'            => ['nullable', 'date'],
            'purchase_cost'            => ['required', 'numeric', 'gt:0'],
            'salvage_value'            => ['nullable', 'numeric', 'min:0', 'lte:purchase_cost'],
            'depreciation_method'      => ['nullable', 'string', 'in:straight_line,declining_balance'],
            'depreciation_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'accumulated_depreciation' => ['nullable', 'numeric', 'min:0'],

            // Warranty & Compliance Documentation
            'warranty_expiry_date'     => ['nullable', 'date'],
            'warranty_document'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes'                    => ['nullable', 'string', 'max:1000'],
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
            'purchase_id'              => 'Purchase Invoice Reference',
            'supplier_id'              => 'Supplier',
            'name'                     => 'Asset Name',
            'category_id'              => 'Asset Category',
            'brand_name'               => 'Brand Name',
            'model_number'             => 'Model Number',
            'serial_number'            => 'Serial Number',
            'branch_id'                => 'Branch / Location',
            'department'               => 'Department',
            'physical_location'        => 'Physical Location',
            'assigned_user_id'         => 'Assigned Employee / User',
            'condition'                => 'Asset Physical Condition',
            'status'                   => 'Operational Status',
            'purchase_date'            => 'Acquisition Date',
            'purchase_cost'            => 'Acquisition Cost',
            'salvage_value'            => 'Salvage Value Threshold',
            'depreciation_method'      => 'Depreciation Method',
            'depreciation_rate'        => 'Depreciation Rate (%)',
            'accumulated_depreciation' => 'Accumulated Initial Depreciation',
            'warranty_expiry_date'     => 'Warranty Expiry Date',
            'warranty_document'        => 'Warranty Documentation File',
        ];
    }
}
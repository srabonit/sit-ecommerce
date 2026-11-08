<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            // Core Identity & Business Details
            'name'                 => ['required', 'string', 'max:255'],
            'company_name'         => ['nullable', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255', 'unique:suppliers,email'],
            'phone'                => ['nullable', 'string', 'max:50'],
            'alt_phone'            => ['nullable', 'string', 'max:50'],
            'tax_number'           => ['nullable', 'string', 'max:100'],
            
            // Address Details
            'address'              => ['nullable', 'string', 'max:500'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'country'              => ['nullable', 'string', 'max:100'],

            // Financial & Opening Ledger Settings
            'opening_balance'      => ['nullable', 'numeric', 'min:0'],
            'opening_balance_type' => ['nullable', 'in:payable,advance'],
            'opening_balance_date' => ['nullable', 'date'],
            'credit_limit'         => ['nullable', 'numeric', 'min:0'],
            'payment_terms_days'   => ['nullable', 'integer', 'min:0'],

            // Status & Notes
            'status'               => ['nullable', 'in:active,inactive'],
            'notes'                => ['nullable', 'string', 'max:1000'],

            // Nested Contact Persons (Optional)
            'contacts'             => ['nullable', 'array'],
            'contacts.*.name'      => ['required_with:contacts', 'string', 'max:255'],
            'contacts.*.phone'     => ['nullable', 'string', 'max:50'],
            'contacts.*.email'     => ['nullable', 'email', 'max:255'],
            'contacts.*.designation'=> ['nullable', 'string', 'max:100'],
            'contacts.*.is_primary'=> ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom message attribute labels for error responses.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'                 => 'Supplier Name',
            'company_name'         => 'Company Name',
            'opening_balance'      => 'Opening Balance',
            'opening_balance_type' => 'Opening Balance Type',
            'credit_limit'         => 'Credit Limit',
            'payment_terms_days'   => 'Payment Terms (Days)',
            'contacts.*.name'      => 'Contact Person Name',
        ];
    }
}
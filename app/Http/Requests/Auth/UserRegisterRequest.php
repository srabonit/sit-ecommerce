<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|string|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'branch_id'    => 'nullable|exists:branches,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'user_type'    => 'required|in:super_admin,admin,branch_manager,pos_operator,warehouse_manager,customer',
            'roles'        => 'nullable|array',
            'roles.*'      => 'string|exists:roles,name',
        ];
    }
}
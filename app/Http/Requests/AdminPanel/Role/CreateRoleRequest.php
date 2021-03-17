<?php

namespace App\Http\Requests\AdminPanel\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('roles')
            ],
            'description' => 'required|string',
            'permissions.*' => [
                'required',
                'string',
                Rule::exists('permissions', 'name')
            ],
        ];
    }
}

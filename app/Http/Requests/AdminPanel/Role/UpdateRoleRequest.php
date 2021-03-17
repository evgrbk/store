<?php

namespace App\Http\Requests\AdminPanel\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
                Rule::unique('roles')->ignore($this->id)
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

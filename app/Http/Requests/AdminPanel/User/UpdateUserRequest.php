<?php

namespace App\Http\Requests\AdminPanel\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|string',
            'email' => [
                'sometimes',
                'string',
                Rule::unique('users')->ignore($this->id)
            ],
            'phone' => [
                'sometimes',
                'string',
                Rule::unique('users')->ignore($this->id)
            ],
            'password' => 'sometimes|string',
            'img' => 'sometimes|image|mimes:jpeg,jpg,png|max:10000',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|string',
            'role_id' => [
                'sometimes',
                'int',
                Rule::exists('roles', 'id')
            ],
        ];
    }
}

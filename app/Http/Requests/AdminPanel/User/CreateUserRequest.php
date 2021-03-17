<?php

namespace App\Http\Requests\AdminPanel\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => [
                'required',
                'string',
                Rule::unique('users')
            ],
            'phone' => [
                'required',
                'string',
                Rule::unique('users')
            ],
            'password' => 'required|string',
            'img' => 'sometimes|image|mimes:jpeg,jpg,png|max:10000',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'role_id' => [
                'required',
                'int',
                Rule::exists('roles', 'id')
            ],
        ];
    }
}

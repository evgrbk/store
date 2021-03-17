<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                'required',
                'string',
                'max:64',
                'email:filter',
                Rule::unique('customers')
            ],
            'password' => 'required|string|min:6',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers')
            ],
            'full_name' => 'required|string|max:64',
            'dob' => 'required|date',
            'is_male' => 'sometimes|boolean',
        ];
    }
}

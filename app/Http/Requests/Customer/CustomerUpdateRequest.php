<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerUpdateRequest extends FormRequest
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
                'sometimes',
                'string',
                'max:64',
                'email:filter',
                Rule::unique('customers')->ignore($this->id)
            ],
            'password' => 'sometimes|string|min:6',
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('customers')->ignore($this->id)
            ],
            'full_name' => 'sometimes|string|max:64',
            'dob' => 'sometimes|date',
            'is_male' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if (!auth()->guard('web')->check() || !auth()->guard('web')->user()->hasPermission('update-customers')) {
            $input = $this->all();
            unset($input['is_active']);
            $this->replace($input);
        }
    }
}

<?php

namespace App\Http\Requests\AdminPanel\Currency;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyStoreRequest extends FormRequest
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
                'max:64',
                Rule::unique('currencies')
            ],
            'code' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/',
                Rule::unique('currencies', 'code')
            ],
            'symbol' => 'required|string|max:8',
            'nominal' => [
                'required',
                'int',
                Rule::in(5, 100, 1000)
            ],
            'primary' => 'required|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'primary' => $this->primary ?? 0
        ]);
    }
}

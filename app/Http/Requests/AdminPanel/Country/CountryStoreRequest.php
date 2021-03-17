<?php

namespace App\Http\Requests\AdminPanel\Country;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryStoreRequest extends FormRequest
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
                Rule::unique('countries')
            ],
            'currency_id' => [
                'required',
                'int',
                Rule::exists('currencies', 'id'),
            ],
            'language_id' => [
                'required',
                'int',
                Rule::exists('languages', 'id'),
            ],
        ];
    }
}

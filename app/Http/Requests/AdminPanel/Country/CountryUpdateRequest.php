<?php

namespace App\Http\Requests\AdminPanel\Country;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryUpdateRequest extends FormRequest
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
                Rule::unique('countries')->ignore($this->id)
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

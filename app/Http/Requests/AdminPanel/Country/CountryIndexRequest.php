<?php

namespace App\Http\Requests\AdminPanel\Country;

use Illuminate\Foundation\Http\FormRequest;

class CountryIndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'limit' => 'sometimes|int'
        ];
    }
}

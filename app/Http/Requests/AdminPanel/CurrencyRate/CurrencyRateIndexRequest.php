<?php

namespace App\Http\Requests\AdminPanel\CurrencyRate;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyRateIndexRequest extends FormRequest
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

<?php

namespace App\Http\Requests\AdminPanel\CurrencyRate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\CurrencyRate;

class CurrencyRateStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'currency_id' => [
                'required',
                'int',
                Rule::exists('currencies', 'id'),
                Rule::unique('currency_rates')
            ],
            'type' => [
                'required',
                'int',
                Rule::in(CurrencyRate::TYPE_MANUAL, CurrencyRate::TYPE_AUTO)
            ],
            'rate' => [
                Rule::requiredIf($this->type === CurrencyRate::TYPE_MANUAL),
                'numeric',
                'between:0,9999999.9999'
            ],
            'limit' => [
                Rule::requiredIf($this->type === CurrencyRate::TYPE_AUTO),
                'int',
                'between:0,100'
            ],
            'interval' => [
                Rule::requiredIf($this->type === CurrencyRate::TYPE_AUTO),
                'int',
                Rule::in(CurrencyRate::INTERVALS)
            ],
        ];
    }
}

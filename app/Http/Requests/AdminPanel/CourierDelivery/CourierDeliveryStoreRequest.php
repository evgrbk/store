<?php

namespace App\Http\Requests\AdminPanel\CourierDelivery;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourierDeliveryStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'country_id' => [
                'required',
                'int',
                Rule::exists('countries', 'id'),
            ],
            'region_id' => [
                'required',
                'int',
                Rule::exists('regions', 'id')
                    ->where('country_id', $this->country_id),
                Rule::unique('courier_deliveries')
                    ->where('country_id', $this->country_id)
            ],
            'limit_sum' => [
                'required',
                'int',
                'min:0'
            ],
            'cost' => [
                'required',
                'int',
                'min:0'
            ]
        ];
    }
}

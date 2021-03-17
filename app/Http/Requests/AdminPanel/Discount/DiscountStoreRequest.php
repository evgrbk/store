<?php

namespace App\Http\Requests\AdminPanel\Discount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Discount;

class DiscountStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => [
                'required',
                'int',
                Rule::in(array_keys(Discount::TYPES))
            ],
            'all_countries' => 'required|boolean',
            'countries' => 'required_if:all_countries,false|array|min:1',
            'countries.*' => [
                'int',
                'distinct',
                Rule::exists('countries', 'id')
            ],
            'all_brands' => 'required|boolean',
            'brands' => 'required_if:all_brands,false|array|min:1',
            'brands.*' => [
                'int',
                'distinct',
                Rule::exists('brands', 'id')
            ],
            'all_categories' => 'required|boolean',
            'categories' => 'required_if:all_categories,false|array|min:1',
            'categories.*' => [
                'int',
                'distinct',
                Rule::exists('categories', 'id')
            ],
            'value_type' => [
                'required',
                'int',
                Rule::in(array_keys(Discount::VALUE_TYPES))
            ],
            'value' => 'required|int|min:1|max:' . ($this->value_type == Discount::VALUE_TYPE_PERCENT ? '100' : '1000000'),
            'display' => 'required|boolean',
            'active_till' => 'sometimes|date|after:' . now(),
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'countries.required_if' => 'Необходимо выбрать страны, если не отмечено поле все страны!',
            'brands.required_if' => 'Необходимо выбрать бренды, если не отмечено поле все бренды!',
            'categories.required_if' => 'Необходимо выбрать категории, если не отмечено поле все категории!',
            'active_till.after' => 'Дата не может быть в прошлом'
        ];
    }
}

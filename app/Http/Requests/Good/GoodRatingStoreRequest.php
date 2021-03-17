<?php

namespace App\Http\Requests\Good;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoodRatingStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'good_id' => [
                'required',
                'int',
                Rule::exists('goods', 'id')
            ],
            'customer_id' => [
                'required',
                'int',
                Rule::exists('customers', 'id')
            ],
            'value' => 'required|int|min:1|max:10'
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
            'customer_id.required' => 'Только авторизованные клиенты могут оценивать товары!',
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
            'customer_id' => auth()->guard('customers')->id(),
        ]);
    }
}

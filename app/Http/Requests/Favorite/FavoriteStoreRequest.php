<?php

namespace App\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FavoriteStoreRequest extends FormRequest
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
            'customer_id.required' => 'Только авторизованные клиенты могут добавлять товары в избранное!',
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

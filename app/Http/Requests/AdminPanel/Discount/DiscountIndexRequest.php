<?php

namespace App\Http\Requests\AdminPanel\Discount;

use Illuminate\Foundation\Http\FormRequest;

class DiscountIndexRequest extends FormRequest
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

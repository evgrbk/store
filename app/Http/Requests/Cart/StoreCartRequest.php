<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'guest_hash' => 'sometimes|string|regex:/^[a-z0-9]{32}$/i',
            'good_id' => [
                'required',
                'int',
                Rule::exists('goods', 'id')
            ],
            'count' => 'sometimes|int|min:1'
        ];
    }
}

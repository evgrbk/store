<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCartRequest extends FormRequest
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
            'count' => 'required|int|min:1'
        ];
    }
}

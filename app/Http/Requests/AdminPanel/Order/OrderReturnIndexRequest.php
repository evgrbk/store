<?php

namespace App\Http\Requests\AdminPanel\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderReturnIndexRequest extends FormRequest
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

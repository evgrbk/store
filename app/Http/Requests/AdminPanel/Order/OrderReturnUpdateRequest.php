<?php

namespace App\Http\Requests\AdminPanel\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\OrderReturn;

class OrderReturnUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => [
                'required',
                'int',
                Rule::exists('orders', 'id')
            ],
            'status' => [
                'required',
                'int',
                Rule::in(array_keys(OrderReturn::STATUSES))
            ],
            'reason' => 'required|string|max:5000'
        ];
    }
}

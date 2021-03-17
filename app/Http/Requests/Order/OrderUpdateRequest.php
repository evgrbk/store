<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'delivery_type' => [
                'required',
                'int',
                Rule::in(Order::DELIVERY_TYPES)
            ],
            'status' => [
                'required',
                'int',
                Rule::in(Order::STATUSES)
            ],
            'full_name' => 'required|string|max:64',
            'email' => 'required|string|max:64|email:filter',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:128',
        ];
    }
}

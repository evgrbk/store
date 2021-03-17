<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\OrderReturn;

class OrderReturnStoreRequest extends FormRequest
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

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'order_id.integer' => 'Неправильный номер заказа!',
            'order_id.exists' => 'Неправильный номер заказа!',
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
            'status' => OrderReturn::STATUS_NEW
        ]);
    }
}

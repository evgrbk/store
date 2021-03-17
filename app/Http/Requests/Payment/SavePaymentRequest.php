<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class SavePaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payment_service' => 'required|string',
            'cart' => 'required|array',
            'cart.*.id' => 'required|int',
            'cart.*.quantity' => 'required|int',
            'email' => 'required|email'
        ];
    }

    public function messages() {
        return [
            'email.required' => 'Поле email обязательно для заполнения',
            'email.email' => 'Полк email имеет не верный формат'
        ];
    }
}

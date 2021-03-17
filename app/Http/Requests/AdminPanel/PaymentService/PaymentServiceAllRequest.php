<?php

namespace App\Http\Requests\AdminPanel\PaymentService;

use Illuminate\Foundation\Http\FormRequest;

class PaymentServiceAllRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'limit' => 'required|int',
        ];
    }
}

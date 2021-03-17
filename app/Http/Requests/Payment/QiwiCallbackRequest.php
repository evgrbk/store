<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class QiwiCallbackRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'hash' => 'required|string',
            'hookId' => 'required|string',
            'messageId' => 'required|string',
            'payment' => 'required|array',
            'test' => 'required|bool',
            'version' => 'required|string',
        ];
    }
}

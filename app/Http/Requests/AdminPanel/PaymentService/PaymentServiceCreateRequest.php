<?php

namespace App\Http\Requests\AdminPanel\PaymentService;

use Illuminate\Foundation\Http\FormRequest;

class PaymentServiceCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "service_title" => "required|unique:payment_services|string|max:100",
            "data" => "required|array",
            "data.token"=>"required|string",
            "data.account"=>"required|string",
        ];
    }
}

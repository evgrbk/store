<?php

namespace App\Http\Requests\AdminPanel\PaymentService;

use Illuminate\Foundation\Http\FormRequest;

class PaymentServiceUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            "service_title" => "required|string|max:100",
            "data" => "required|array",
        ];

        if ($this->service_title == 'qiwi') {
            $rules["data.public_key"] = "required|string";
            $rules["data.secret_key"] = "required|string";
        }

        if ($this->service_title == 'payeer') {
            $rules["data.shop_id"] = "required|string";
            $rules["data.secret_key"] = "required|string";
        }

        return $rules;
    }
}

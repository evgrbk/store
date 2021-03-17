<?php

namespace App\Http\Requests\AdminPanel\CourierDelivery;

use Illuminate\Foundation\Http\FormRequest;

class CourierDeliveryIndexRequest extends FormRequest
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

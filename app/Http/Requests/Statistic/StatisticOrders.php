<?php

namespace App\Http\Requests\Statistic;

use Illuminate\Foundation\Http\FormRequest;

class StatisticOrders extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => 'string|max:100',
            'email' => 'string|max:255',
            'date' => 'string|max:255',
            'payment' => 'string|max:255',
            'order_id' => 'integer',
        ];
    }
}

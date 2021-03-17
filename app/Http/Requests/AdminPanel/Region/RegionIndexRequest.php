<?php

namespace App\Http\Requests\AdminPanel\Region;

use Illuminate\Foundation\Http\FormRequest;

class RegionIndexRequest extends FormRequest
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

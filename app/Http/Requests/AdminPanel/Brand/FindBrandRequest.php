<?php

namespace App\Http\Requests\AdminPanel\Brand;

use Illuminate\Foundation\Http\FormRequest;

class FindBrandRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|string'
        ];
    }
}

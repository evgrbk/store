<?php

namespace App\Http\Requests\AdminPanel\Brand;

use Illuminate\Foundation\Http\FormRequest;

class CreateBrandRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'description' => 'required|string',
            'img' => 'sometimes|image|mimes:jpeg,jpg,png|max:10000',
        ];
    }
}

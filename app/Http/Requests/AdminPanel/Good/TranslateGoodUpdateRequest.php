<?php

namespace App\Http\Requests\AdminPanel\Good;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslateGoodUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'good_id' => [
                'required',
                'int',
                Rule::exists('goods', 'id')
            ],
            'language_id' => [
                'required',
                'int',
                Rule::exists('languages', 'id')
            ],
            'good_title' => 'nullable|string|max:255',
            'good_description' => 'nullable|string|max:5000',
        ];
    }
}

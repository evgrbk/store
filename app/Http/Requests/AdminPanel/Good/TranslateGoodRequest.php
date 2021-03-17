<?php

namespace App\Http\Requests\AdminPanel\Good;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslateGoodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'language_id' => [
                'required',
                'int',
                Rule::exists('languages', 'id')
            ],
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ];
    }
}

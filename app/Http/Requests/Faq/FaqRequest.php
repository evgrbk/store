<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question' => 'required|string|unique:faq',
            'answer' => 'required|string',

            'img' => 'sometimes|array',
            'img.*.img' => 'mimes:jpeg,jpg,png,gif|max:10000',
        ];
    }
}

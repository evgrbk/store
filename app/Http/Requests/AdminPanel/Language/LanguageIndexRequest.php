<?php

namespace App\Http\Requests\AdminPanel\Language;

use Illuminate\Foundation\Http\FormRequest;

class LanguageIndexRequest extends FormRequest
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

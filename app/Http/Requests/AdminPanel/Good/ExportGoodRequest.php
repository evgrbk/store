<?php

namespace App\Http\Requests\AdminPanel\Good;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\Good;

class ExportGoodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'fileFormat' => [
                'required',
                'string',
                Rule::in(Good::EXPORT_TYPES)
            ],
            'columns' => 'required|array|min:1'
        ];
    }
}

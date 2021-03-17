<?php

namespace App\Http\Requests\AdminPanel\AutoImport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\AutoImport;
use App\Http\Rules\FieldsValid;
use App\Http\Rules\ParserValid;

class AutoImportStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'schedule' => [
                'required',
                'integer',
                Rule::in(array_keys(AutoImport::SCHEDULE))
            ],
            'url' => 'required|string|url|max:255',
            'active' => 'required|boolean',
            'parser_type' => [
                'required',
                'string',
                new ParserValid
            ],
            'fields' => 'required|array|min:2',
            'fields.src' => 'required|array|min:1',
            'fields.dest' => 'required|array|min:1',
            'selected_fields' => ['required',
                'array',
                'min:1',
                new FieldsValid($this->fields),
            ]
        ];
    }
}

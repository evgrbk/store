<?php

namespace App\Http\Requests\AdminPanel\AutoImport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\AutoImport;
use App\Http\Rules\FieldsValid;
use App\Http\Rules\ParserValid;

class AutoImportUpdateRequest extends FormRequest
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
                'sometimes',
                'integer',
                Rule::in(array_keys(AutoImport::SCHEDULE))
            ],
            'url' => 'sometimes|string|url|max:255',
            'active' => 'required|boolean',
            'parser_type' => [
                'sometimes',
                'string',
                new ParserValid
            ],
            'fields' => 'sometimes|array|min:2',
            'fields.src' => 'sometimes|array|min:1',
            'fields.dest' => 'sometimes|array|min:1',
            'selected_fields' => ['sometimes',
                'array',
                'min:1',
                new FieldsValid($this->fields),
            ]
        ];
    }
}

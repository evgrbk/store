<?php

namespace App\Http\Requests\AdminPanel\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Country;

class LanguageUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:32',
                Rule::unique('languages')->ignore($this->id)
            ],
            'code' => [
                'sometimes',
                'string',
                'size:2',
                'regex:/^[a-z]{2}$/',
                Rule::unique('languages')->ignore($this->id)
            ],
            'primary' => 'required|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'primary' => $this->primary ?? 0,
        ]);
    }
}

<?php

namespace App\Http\Requests\AdminPanel\Setting;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:64',
                Rule::unique('settings'),
            ],
            'content' => 'required|array',
        ];

        switch ($this->name) {
            case Setting::NAME_PRICING:
                $rules['content.margin'] = 'required|int|between:0,100';
                break;
        }

        return $rules;
    }
}

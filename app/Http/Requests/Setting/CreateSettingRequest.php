<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class CreateSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'seo_h1' => 'required|string',
            'seo_title' => 'required|string',
            'seo_description' => 'required|string',
            'title_setting' => 'required|string|unique:page_settings',
            'config' => 'nullable|array',

            'config.telegram' => 'string',
            'config.email' => 'email',

            'config.button' => 'array',
            'config.button.text' => 'string',
            'config.button.show' => 'boolean',

            'img' => 'mimes:jpeg,jpg,png,gif|max:10000',

            'items' => 'array',
            'items.*' => 'array',
            'items.*.title' => 'string',
            'items.*.description' => 'string',
            'items.*.img' => 'mimes:jpeg,jpg,png,gif|max:10000',
        ];
    }
}

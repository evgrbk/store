<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
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
            'title_setting' => 'required|string',
            'config' => 'nullable',

            'items' => 'array',
            'items.*' => 'array',
            'items.*.title' => 'string',
            'items.*.description' => 'string',
            'items.*.link' => 'nullable|url',
            'items.*.img' => 'nullable|mimes:jpeg,jpg,png,gif|max:10000',

            'deleted_files' => 'sometimes|array',
            'brand_logos' => 'sometimes|array',
            'payment_logos' => 'sometimes|array',
        ];
    }

    public function attributes() {
        return [
            'items.*.link' => 'url'
        ];
    }
}

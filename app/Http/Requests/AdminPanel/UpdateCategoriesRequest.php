<?php

namespace App\Http\Requests\AdminPanel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoriesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'category_id' => 'nullable|int',
            'seo_h1' => 'nullable',
            'seo_title' => 'nullable',
            'seo_description' => 'nullable',
            'seo_slug' => 'nullable',
            'order' => 'nullable',
            'features_group_id' => 'nullable',
            'weight' => 'nullable|numeric|min:0|max:99999999.99',
        ];
    }
}

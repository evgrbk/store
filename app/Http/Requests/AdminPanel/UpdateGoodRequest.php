<?php

namespace App\Http\Requests\AdminPanel;

use App\Services\UploaderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGoodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @param UploaderService $uploaderService
     * @return array
     */
    public function rules(UploaderService $uploaderService)
    {
        return [
            'good_title' => 'required|string|max:255',
            'good_description' => 'required|string',
            'category_id' => 'required|int',
            'brand_id' => 'sometimes|int|exists:brands,id',
            'seo_h1' => 'required|string|max:255',
            'seo_title' => 'required|string|max:255',
            'seo_description' => 'required|string',
            'seo_slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('goods')->ignore($this->route('good'))
            ],
            'active' => 'required',
            'good_left' => 'required|int',
            'good_type' => 'required|string',
            'price_integer' => 'required|int',
            'price_decimal' => 'required|int',
            'deleted_files' => 'sometimes|array',
            'sku' => 'required|string|max:32',
            'weight' => 'nullable|numeric|min:0|max:99999999.99',
            'file.*' => 'file|max:' . config('parameters.files.upload_max_size')
                . '|mimes:' . $uploaderService->getFilesMimeTypesString(),
            'img.*' => 'file|max:' . config('parameters.files.upload_max_size')
                . 'mimes:' . $uploaderService->getImagesMimeTypesString()
        ];
    }
}

<?php

namespace App\Http\Requests\AdminPanel\FeatureGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\CurrencyRate;

class FeatureGroupStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'features' => 'required|array',
            'features.*' => [
                'required',
                'int',
                Rule::exists('features', 'id'),
            ],
        ];
    }
}

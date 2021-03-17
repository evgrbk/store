<?php

namespace App\Http\Requests\AdminPanel\Region;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegionUpdateRequest extends FormRequest
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
                'required',
                'string',
                'max:64',
                Rule::unique('regions')
                    ->where('country_id', $this->country_id)
                    ->ignore($this->id)
            ],
            'country_id' => [
                'required',
                'int',
                Rule::exists('countries', 'id'),
            ],
        ];
    }
}

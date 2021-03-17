<?php

namespace App\Http\Requests\AdminPanel\AutoImport;

use Illuminate\Foundation\Http\FormRequest;

class AutoImportPrepareRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'url' => 'required|string|url|max:255',
        ];
    }
}

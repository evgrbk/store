<?php

namespace App\Http\Requests\AdminPanel\Good;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PrepareImportGoodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|file|mimes:xml|max:262144', //Max file size 256mb
        ];
    }

    /**
     * Messages for validator
     *
     * @return array
     */
    public function messages()
    {
        return [
            'file.required' => 'Файл для импорта отсутствует!',
            'file.mimes' => 'Неверный формат файла!',
            'file.max' => "Максимальный размер файла 256мб",
        ];
    }

    /**
     * [failedValidation [Overriding the event validator for custom error response]]
     * @param Validator $validator [description]
     * @return [object][object of various validation errors]
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->messages()->first(),
        ], 422));
    }

}

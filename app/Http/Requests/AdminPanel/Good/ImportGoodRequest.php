<?php

namespace App\Http\Requests\AdminPanel\Good;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportGoodRequest extends FormRequest
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
            'file' => 'required|string',
            'type' => 'required|string',
            'fields' => 'required|array|min:1',
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
            'file.required' => 'Импортируемый файл не найден!',
            'type.required' => 'Тип обработчика импортируемого файла не поддерживается!',
            'fields.*' => "Не указаны данные для импорта!",
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

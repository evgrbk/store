<?php

namespace App\Http\Requests\AdminPanel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CheckUniqueGoodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'seo_h1' => 'sometimes|string|max:255|unique:goods',
            'seo_title' => 'sometimes|string|max:255|unique:goods'
        ];
    }

    /**
     * Override exception error message and error code
     *
     * @param Validator $validator
     * @throws ValidationException
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        $response = new JsonResponse([
                'data' => [
                    'warning' => true
                ]
            ], JsonResponse::HTTP_OK);

        throw new ValidationException($validator, $response);
    }
}

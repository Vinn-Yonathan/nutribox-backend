<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MenuUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'max:100'],
            'description' => ['sometimes', 'nullable', 'max:1000'],
            'image' => ['sometimes', 'required', 'image'],
            'stock' => ['sometimes', 'required', 'integer', 'between:0,10000'],
            'calories' => ['sometimes', 'required', 'integer', 'between:0,10000'],
            'price' => ['sometimes', 'required', 'integer', 'between:0,100000000'],
            'is_featured' => ['sometimes', 'required', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            'errors' => $validator->getMessageBag()
        ], 400));
    }
}

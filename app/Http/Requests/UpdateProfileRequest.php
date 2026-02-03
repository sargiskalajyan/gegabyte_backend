<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateProfileRequest extends FormRequest
{


    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string', 'max:255'],
            'language_id' => ['nullable', 'exists:languages,id'],

            'location_id' => ['nullable', 'exists:locations,id'],

            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }


    /**
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return mixed
     * @throws ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        $count = $errors->count();

        $first = $errors->first();

        $summary = $first;
        if ($count > 1) {
            $summary .= ' ' . __('validation.more_errors', ['count' => $count - 1]);
        }

        throw new ValidationException($validator, response()->json([
            'message' => $summary,
            'errors'  => $errors->messages(),
        ], 422));
    }

}

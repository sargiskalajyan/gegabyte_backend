<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'     => 'required|email',
            'password'  => 'required',
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
        $count  = $errors->count();
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListingSearchRequest extends FormRequest
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
            'make_id'         => 'nullable|array',
            'make_id.*'       => 'integer|exists:makes,id',

            'model_id'        => 'nullable|array',
            'model_id.*'      => 'integer|exists:car_models,id',

            'fuel_id'         => 'nullable|integer|exists:fuels,id',
            'transmission_id' => 'nullable|integer|exists:transmissions,id',
            'location_id'     => 'nullable|integer|exists:locations,id',

            'price_from'      => 'nullable|numeric|min:0',
            'price_to'        => 'nullable|numeric|gte:price_from',

            'year_from'       => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'year_to'         => 'nullable|integer|gte:year_from|max:' . (date('Y') + 1),

            'keyword'         => 'nullable|string|max:255',
            'per_page'        => 'nullable|integer|min:1|max:100',
            'exchange'        => 'nullable|in:0,1',
            'top'             => 'nullable|in:0,1',
        ];
    }
}

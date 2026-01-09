<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ListingUpdateRequest extends FormRequest
{

    /**
     * @return string[]
     */
    public function rules(): array
    {

        return [
            'category_id'     => 'nullable|exists:categories,id',
            'fuel_id'         => 'nullable|exists:fuels,id',
            'transmission_id' => 'nullable|exists:transmissions,id',
            'drivetrain_id'   => 'nullable|exists:drivetrains,id',
            'condition_id'    => 'nullable|exists:conditions,id',
            'location_id'     => 'nullable|exists:locations,id',
            'make_id'         => 'nullable|exists:makes,id',
            'car_model_id'    => 'nullable|exists:car_models,id',
            'engine_id'       => 'nullable|exists:engines,id',
            'engine_size_id'  => 'nullable|exists:engine_sizes,id',
            'color_id'        => 'nullable|exists:colors,id',
            'currency_id'     => 'nullable|exists:currencies,id',
            'driver_type_id'  => 'nullable|exists:driver_types,id',

            'gas_equipment_id'      => 'nullable|exists:gas_equipments,id',
            'wheel_size_id'         => 'nullable|exists:wheel_sizes,id',
            'headlight_id'          => 'nullable|exists:headlights,id',
            'interior_color_id'     => 'nullable|exists:interior_colors,id',
            'interior_material_id'  => 'nullable|exists:interior_materials,id',
            'steering_wheel_id'     => 'nullable|exists:steering_wheels,id',

            'year'       => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'mileage'    => 'nullable|integer|min:0',
            'price'      => 'nullable|numeric|min:0',
            'vin' => [
                'nullable',
                'string',
                'min:10',
                'max:17',
                Rule::unique('listings', 'vin')->ignore(
                    $this->route('listing')->id ?? null
                ),
            ],
            'exchange'   => 'sometimes|in:0,1',

            'description'       => 'nullable|string|max:500',

            // Optional images, not required
            'images'   => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }


    /**
     * @return void
     * @throws ValidationException
     */
    protected function passedValidation()
    {
        if ($this->hasFile('images')) {
            foreach ($this->file('images') as $image) {
                [$width, $height] = getimagesize($image);

                if ($width < 600 || $height < 500) {
                    throw ValidationException::withMessages([
                        'images' => [__('listings.image_too_small')],
                    ]);
                }
            }
        }
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

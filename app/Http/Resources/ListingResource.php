<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{

    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'description' => $this->description,
            'price'       => $this->price,
            'year'        => $this->year,
            'mileage'     => $this->mileage,
            'vin'         => $this->vin,
            'exchange'    => (integer)$this->exchange,

            // MAIN RELATIONS
            'make'               => $this->relationData($this->make_id, $this->make_name),
            'model'              => $this->relationData($this->car_model_id, $this->model_name),
            'fuel'               => $this->relationData($this->fuel_id, $this->fuel_name),
            'transmission'       => $this->relationData($this->transmission_id, $this->transmission_name),
            'drivetrain'         => $this->relationData($this->drivetrain_id, $this->drivetrain_name),
            'condition'          => $this->relationData($this->condition_id, $this->condition_name),
            'color'              => $this->relationData($this->color_id, $this->color_name),
            'driver_type'        => $this->relationData($this->driver_type_id, $this->driver_type_name),
            'category'           => $this->relationData($this->category_id, $this->category_name),
            'location'           => $this->relationData($this->location_id, $this->location_name),
            'engine'             => $this->relationData($this->engine_id, $this->engine_name),
            'engine_size'        => $this->relationData($this->engine_size_id, $this->engine_size_name),

            // NEW RELATIONS
            'gas_equipment'      => $this->relationData($this->gas_equipment_id, $this->gas_equipment_name),
            'wheel_size'         => $this->relationData($this->wheel_size_id, $this->wheel_size_name),
            'headlight'          => $this->relationData($this->headlight_id, $this->headlight_name),
            'interior_color'     => $this->relationData($this->interior_color_id, $this->interior_color_name),
            'interior_material'  => $this->relationData($this->interior_material_id, $this->interior_material_name),
            'steering_wheel'     => $this->relationData($this->steering_wheel_id, $this->steering_wheel_name),
            'currency'           => $this->relationData($this->currency_id, $this->currency_name),

            // PHOTOS
            'photos' => $this->photos->map(fn ($photo) => [
                'id'        => $photo->id,
                'url'       => $photo->url,
                'thumbnail' => $photo->thumbnail,
            ]),


            'user' => $this->whenLoaded('user', function () {
                return new UserProfileResource($this->user);
            }),

            'published_until' => $this->published_until,
            'is_top' => (bool)$this->is_top,
            'top_expires_at' => $this->top_expires_at,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }


    /**
     * @param $id
     * @param $name
     * @return array|null
     */
    private function relationData($id, $name)
    {
        return $id ? [
            'id'   => $id,
            'name' => $name,
        ] : null;
    }
}

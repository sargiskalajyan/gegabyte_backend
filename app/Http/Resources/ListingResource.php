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

            // Related objects: only include if ID exists
            'make' => $this->make_id ? [
                'id'   => $this->make_id,
                'name' => $this->make_name,
            ] : null,

            'model' => $this->car_model_id ? [
                'id'   => $this->car_model_id,
                'name' => $this->model_name,
            ] : null,

            'fuel' => $this->fuel_id ? [
                'id'   => $this->fuel_id,
                'name' => $this->fuel_name,
            ] : null,

            'transmission' => $this->transmission_id ? [
                'id'   => $this->transmission_id,
                'name' => $this->transmission_name,
            ] : null,

            'drivetrain' => $this->drivetrain_id ? [
                'id'   => $this->drivetrain_id,
                'name' => $this->drivetrain_name,
            ] : null,

            'condition' => $this->condition_id ? [
                'id'   => $this->condition_id,
                'name' => $this->condition_name,
            ] : null,

            'color' => $this->color_id ? [
                'id'   => $this->color_id,
                'name' => $this->color_name,
            ] : null,

            'driver_type' => $this->driver_type_id ? [
                'id'   => $this->driver_type_id,
                'name' => $this->driver_type_name,
            ] : null,

            'category' => $this->category_id ? [
                'id'   => $this->category_id,
                'name' => $this->category_name,
            ] : null,

            'location' => $this->location_id ? [
                'id'   => $this->location_id,
                'name' => $this->location_name,
            ] : null,

            'photos' => $this->photos->pluck('url'),

            'user' => $this->user ? [
                'id'       => $this->user->id,
                'username' => $this->user->username,
            ] : null,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}

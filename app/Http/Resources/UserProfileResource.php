<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'username'     => $this->username,
            'email'        => $this->email,
            'phone_number' => $this->phone_number,
            'profile_image'=> $this->profile_image
                ? url('storage/' . $this->profile_image)
                : null,

            'language' => $this->language ? [
                'id'   => $this->language->id,
                'code' => $this->language->code,
                'name' => $this->language->name,
            ] : null,

            'location' => $this->location ? [
                'id'   => $this->location->id,
                'name' => $this->location_name,
            ] : null,

            'listings_count' => $this->whenCounted('listings'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'username'    => $this->username,
            'email'       => $this->email,
            'phone_number'=> $this->phone_number,
            'language_id' => $this->language_id,
            'phone_number_verified_at' => $this->phone_number_verified_at,
            'email_verified_at' => $this->email_verified_at,
            'created_at'  => $this->created_at?->toDateTimeString(),
            'profile_image' => $this->profile_image
                ? asset('storage/' . $this->profile_image)
                : null,

            'listings_count' => $this->whenCounted('listings'),
            'active_package' => $this->when(
                $this->relationLoaded('userPackages') || true,
                fn () => new UserPackageResource($this->activePackage())
            ),
        ];
    }
}

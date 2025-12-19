<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'package_id' => $this->package_id,
            'status' => $this->status,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'package' => [
                'name' => $this->package?->name,
                'price' => $this->package?->price,
                'max_active_listings' => $this->package?->max_active_listings,
                'included_featured_days' => $this->package?->included_featured_days,
            ]
        ];
    }
}

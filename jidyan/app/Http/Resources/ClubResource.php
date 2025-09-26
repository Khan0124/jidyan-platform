<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource?->getKey(),
            'name' => $this->resource?->name,
            'city' => $this->resource?->city,
            'country' => $this->resource?->country,
            'verified_at' => optional($this->resource?->verified_at)->toIso8601String(),
        ];
    }
}

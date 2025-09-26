<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'requirements' => $this->resource->requirements ?? [],
            'location' => [
                'city' => $this->resource->location_city,
                'country' => $this->resource->location_country,
            ],
            'deadline_at' => optional($this->resource->deadline_at)->toIso8601String(),
            'status' => $this->resource->status,
            'visibility' => $this->resource->visibility,
            'applications_count' => $this->whenCounted('applications'),
            'club' => ClubResource::make($this->whenLoaded('club')),
            'created_at' => optional($this->resource->created_at)->toIso8601String(),
            'updated_at' => optional($this->resource->updated_at)->toIso8601String(),
        ];
    }
}

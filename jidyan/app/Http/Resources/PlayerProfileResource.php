<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlayerProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        $mediaLoaded = $this->relationLoaded('media');

        return [
            'id' => $this->resource->getKey(),
            'user' => UserSummaryResource::make($this->whenLoaded('user')),
            'nationality' => $this->resource->nationality,
            'city' => $this->resource->city,
            'country' => $this->resource->country,
            'position' => $this->resource->position,
            'preferred_foot' => $this->resource->preferred_foot,
            'height_cm' => $this->resource->height_cm,
            'weight_kg' => $this->resource->weight_kg,
            'current_club' => $this->resource->current_club,
            'bio' => $this->resource->bio,
            'availability' => [
                'value' => $this->resource->availability,
                'label' => $this->resource->availabilityLabel(),
            ],
            'badges' => [
                'verified_identity' => (bool) $this->resource->verified_identity_at,
                'verified_academy' => (bool) $this->resource->verified_academy_at,
            ],
            'visibility' => $this->resource->visibility,
            'has_ready_video' => $this->when($mediaLoaded, fn () => $this->resource->media
                ->where('type', 'video')
                ->where('status', 'ready')
                ->isNotEmpty()),
            'media' => PlayerMediaResource::collection($this->whenLoaded('media')),
            'stats' => PlayerStatResource::collection($this->whenLoaded('stats')),
            'view_count' => $this->resource->view_count,
            'last_active_at' => optional($this->resource->last_active_at)->toIso8601String(),
            'updated_at' => optional($this->resource->updated_at)->toIso8601String(),
            'relevance' => $this->when(isset($this->resource->relevance), fn () => round((float) $this->resource->relevance, 4)),
        ];
    }
}

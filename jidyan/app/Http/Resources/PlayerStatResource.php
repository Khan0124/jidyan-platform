<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlayerStatResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'season' => $this->resource->season,
            'matches' => $this->resource->matches,
            'goals' => $this->resource->goals,
            'assists' => $this->resource->assists,
            'notes' => $this->resource->notes,
            'verified_by' => UserSummaryResource::make($this->whenLoaded('verifier')),
            'created_at' => optional($this->resource->created_at)->toIso8601String(),
            'updated_at' => optional($this->resource->updated_at)->toIso8601String(),
        ];
    }
}

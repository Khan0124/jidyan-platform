<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'status' => $this->resource->status,
            'note' => $this->resource->note,
            'player_id' => $this->resource->player_id,
            'opportunity_id' => $this->resource->opportunity_id,
            'agent_id' => $this->resource->agent_id,
            'media' => PlayerMediaResource::make($this->whenLoaded('media')),
            'player' => PlayerProfileResource::make($this->whenLoaded('player')),
            'opportunity' => OpportunityResource::make($this->whenLoaded('opportunity')),
            'created_at' => optional($this->resource->created_at)->toIso8601String(),
            'updated_at' => optional($this->resource->updated_at)->toIso8601String(),
        ];
    }
}

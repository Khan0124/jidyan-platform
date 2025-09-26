<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlayerMediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'type' => $this->resource->type,
            'status' => $this->resource->status,
            'provider' => $this->resource->provider,
            'original_filename' => $this->resource->original_filename,
            'path' => $this->resource->path,
            'hls_path' => $this->resource->hls_path,
            'poster_path' => $this->resource->poster_path,
            'duration_seconds' => $this->resource->duration_seconds,
            'quality_label' => $this->resource->quality_label,
            'meta' => $this->resource->meta ?? [],
            'created_at' => optional($this->resource->created_at)->toIso8601String(),
            'updated_at' => optional($this->resource->updated_at)->toIso8601String(),
        ];
    }
}

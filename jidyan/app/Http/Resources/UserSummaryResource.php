<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource?->getKey(),
            'name' => $this->resource?->name,
            'email' => $this->when(optional($request->user())->hasRole('admin'), $this->resource?->email),
        ];
    }
}

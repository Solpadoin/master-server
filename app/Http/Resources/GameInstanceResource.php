<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameInstanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'steam_app_id' => $this->steam_app_id,
            'name' => $this->name,
            'memory_limit_mb' => $this->memory_limit_mb,
            'is_active' => $this->is_active,
            'steam_store_url' => "https://store.steampowered.com/app/{$this->steam_app_id}",
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

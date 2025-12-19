<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\GameServerDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameServerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var GameServerDTO $this */
        return [
            'id' => $this->serverId,
            'name' => $this->name,
            'address' => $this->address,
            'port' => $this->port,
            'server_type' => $this->serverType->value,
            'status' => $this->status->value,
            'players' => [
                'current' => $this->currentPlayers,
                'max' => $this->maxPlayers,
            ],
            'map' => $this->map,
            'region' => $this->region,
            'ping' => $this->ping,
            'metadata' => $this->metadata,
        ];
    }
}

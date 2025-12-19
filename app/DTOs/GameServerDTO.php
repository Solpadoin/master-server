<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ServerType;
use App\Enums\ServerStatus;

readonly class GameServerDTO
{
    public function __construct(
        public string $serverId,
        public int $gameId,
        public string $name,
        public string $address,
        public int $port,
        public ServerType $serverType,
        public ServerStatus $status,
        public int $currentPlayers,
        public int $maxPlayers,
        public ?string $map = null,
        public ?string $region = null,
        public ?int $ping = null,
        public array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverId: $data['server_id'],
            gameId: (int) $data['game_id'],
            name: $data['name'],
            address: $data['address'],
            port: (int) $data['port'],
            serverType: ServerType::from($data['server_type']),
            status: ServerStatus::from($data['status'] ?? 'online'),
            currentPlayers: (int) ($data['current_players'] ?? 0),
            maxPlayers: (int) $data['max_players'],
            map: $data['map'] ?? null,
            region: $data['region'] ?? null,
            ping: isset($data['ping']) ? (int) $data['ping'] : null,
            metadata: $data['metadata'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'server_id' => $this->serverId,
            'game_id' => $this->gameId,
            'name' => $this->name,
            'address' => $this->address,
            'port' => $this->port,
            'server_type' => $this->serverType->value,
            'status' => $this->status->value,
            'current_players' => $this->currentPlayers,
            'max_players' => $this->maxPlayers,
            'map' => $this->map,
            'region' => $this->region,
            'ping' => $this->ping,
            'metadata' => $this->metadata,
            'last_heartbeat' => now()->toIso8601String(),
        ];
    }
}

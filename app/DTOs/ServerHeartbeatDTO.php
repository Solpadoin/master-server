<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ServerStatus;
use Illuminate\Http\Request;

readonly class ServerHeartbeatDTO
{
    public function __construct(
        public string $serverId,
        public ServerStatus $status,
        public int $currentPlayers,
        public ?string $map = null,
        public ?int $ping = null,
        public array $metadata = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            serverId: $request->input('server_id'),
            status: ServerStatus::from($request->input('status', 'online')),
            currentPlayers: (int) $request->input('current_players', 0),
            map: $request->input('map'),
            ping: $request->has('ping') ? (int) $request->input('ping') : null,
            metadata: $request->input('metadata', []),
        );
    }

    public function toArray(): array
    {
        return [
            'server_id' => $this->serverId,
            'status' => $this->status->value,
            'current_players' => $this->currentPlayers,
            'map' => $this->map,
            'ping' => $this->ping,
            'metadata' => $this->metadata,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ServerType;
use Illuminate\Http\Request;

readonly class GameServerFilterDTO
{
    public function __construct(
        public int $gameId,
        public ?int $maxPing = null,
        public ?string $region = null,
        public ?ServerType $serverType = null,
        public ?int $maxPlayers = null,
        public ?bool $hasPlayers = null,
        public ?bool $notFull = null,
    ) {}

    public static function fromRequest(Request $request, int $gameId): self
    {
        return new self(
            gameId: $gameId,
            maxPing: $request->has('max_ping') ? (int) $request->input('max_ping') : null,
            region: $request->input('region'),
            serverType: $request->has('type') ? ServerType::tryFrom($request->input('type')) : null,
            maxPlayers: $request->has('max_players') ? (int) $request->input('max_players') : null,
            hasPlayers: $request->has('has_players') ? filter_var($request->input('has_players'), FILTER_VALIDATE_BOOLEAN) : null,
            notFull: $request->has('not_full') ? filter_var($request->input('not_full'), FILTER_VALIDATE_BOOLEAN) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'game_id' => $this->gameId,
            'max_ping' => $this->maxPing,
            'region' => $this->region,
            'server_type' => $this->serverType?->value,
            'max_players' => $this->maxPlayers,
            'has_players' => $this->hasPlayers,
            'not_full' => $this->notFull,
        ], fn ($value) => $value !== null);
    }
}

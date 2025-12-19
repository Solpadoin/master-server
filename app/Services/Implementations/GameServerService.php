<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\DTOs\GameServerDTO;
use App\DTOs\GameServerFilterDTO;
use App\DTOs\ServerHeartbeatDTO;
use App\Enums\ServerStatus;
use App\Services\Contracts\GameCacheServiceInterface;
use App\Services\Contracts\GameServerServiceInterface;
use App\Services\Contracts\GameServiceInterface;
use Illuminate\Support\Collection;

class GameServerService implements GameServerServiceInterface
{
    public function __construct(
        private readonly GameCacheServiceInterface $cacheService,
        private readonly GameServiceInterface $gameService,
    ) {}

    public function getServers(GameServerFilterDTO $filter): Collection
    {
        $servers = $this->cacheService->getServers($filter->gameId);

        if ($servers === null) {
            return collect();
        }

        return collect($servers)->filter(function (GameServerDTO $server) use ($filter) {
            return $this->matchesFilter($server, $filter);
        })->values();
    }

    public function getServer(int $gameId, string $serverId): ?GameServerDTO
    {
        return $this->cacheService->getServer($gameId, $serverId);
    }

    public function registerServer(GameServerDTO $serverData): GameServerDTO
    {
        $this->cacheService->setServer($serverData->gameId, $serverData);

        return $serverData;
    }

    public function processHeartbeat(int $gameId, ServerHeartbeatDTO $heartbeat): GameServerDTO
    {
        $existingServer = $this->cacheService->getServer($gameId, $heartbeat->serverId);

        if (! $existingServer) {
            throw new \RuntimeException("Server {$heartbeat->serverId} not found");
        }

        $updatedServer = new GameServerDTO(
            serverId: $existingServer->serverId,
            gameId: $existingServer->gameId,
            name: $existingServer->name,
            address: $existingServer->address,
            port: $existingServer->port,
            serverType: $existingServer->serverType,
            status: $heartbeat->status,
            currentPlayers: $heartbeat->currentPlayers,
            maxPlayers: $existingServer->maxPlayers,
            map: $heartbeat->map ?? $existingServer->map,
            region: $existingServer->region,
            ping: $heartbeat->ping ?? $existingServer->ping,
            metadata: array_merge($existingServer->metadata, $heartbeat->metadata),
        );

        $this->cacheService->setServer($gameId, $updatedServer);

        return $updatedServer;
    }

    public function unregisterServer(int $gameId, string $serverId): bool
    {
        $server = $this->cacheService->getServer($gameId, $serverId);

        if (! $server) {
            return false;
        }

        $this->cacheService->removeServer($gameId, $serverId);

        return true;
    }

    public function cleanupStaleServers(): int
    {
        $timeout = config('master_server.heartbeat.timeout', 90);
        $cleaned = 0;

        $games = $this->gameService->getActiveGames();

        foreach ($games as $game) {
            $staleServers = $this->cacheService->getStaleServers($game->steam_app_id, $timeout);

            foreach ($staleServers as $serverId) {
                $this->cacheService->removeServer($game->steam_app_id, $serverId);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    private function matchesFilter(GameServerDTO $server, GameServerFilterDTO $filter): bool
    {
        if ($filter->maxPing !== null && $server->ping !== null && $server->ping > $filter->maxPing) {
            return false;
        }

        if ($filter->region !== null && $server->region !== $filter->region) {
            return false;
        }

        if ($filter->serverType !== null && $server->serverType !== $filter->serverType) {
            return false;
        }

        if ($filter->maxPlayers !== null && $server->maxPlayers > $filter->maxPlayers) {
            return false;
        }

        if ($filter->hasPlayers === true && $server->currentPlayers === 0) {
            return false;
        }

        if ($filter->notFull === true && $server->currentPlayers >= $server->maxPlayers) {
            return false;
        }

        if ($server->status !== ServerStatus::ONLINE) {
            return false;
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\DTOs\GameServerDTO;
use App\DTOs\GameServerFilterDTO;
use App\DTOs\ServerHeartbeatDTO;
use Illuminate\Support\Collection;

interface GameServerServiceInterface
{
    /**
     * Get filtered list of servers for a game.
     *
     * @return Collection<int, GameServerDTO>
     */
    public function getServers(GameServerFilterDTO $filter): Collection;

    /**
     * Get a specific server.
     */
    public function getServer(int $gameId, string $serverId): ?GameServerDTO;

    /**
     * Register a new server.
     */
    public function registerServer(GameServerDTO $serverData): GameServerDTO;

    /**
     * Process server heartbeat.
     */
    public function processHeartbeat(int $gameId, ServerHeartbeatDTO $heartbeat): GameServerDTO;

    /**
     * Unregister a server.
     */
    public function unregisterServer(int $gameId, string $serverId): bool;

    /**
     * Clean up stale servers.
     */
    public function cleanupStaleServers(): int;
}

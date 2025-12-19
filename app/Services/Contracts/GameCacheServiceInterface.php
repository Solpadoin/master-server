<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\DTOs\GameServerDTO;
use App\DTOs\GameServerFilterDTO;

interface GameCacheServiceInterface
{
    /**
     * Get all servers for a game from cache.
     *
     * @return array<GameServerDTO>|null
     */
    public function getServers(int $gameId): ?array;

    /**
     * Get a specific server from cache.
     */
    public function getServer(int $gameId, string $serverId): ?GameServerDTO;

    /**
     * Store a server in cache.
     */
    public function setServer(int $gameId, GameServerDTO $server): void;

    /**
     * Remove a server from cache.
     */
    public function removeServer(int $gameId, string $serverId): void;

    /**
     * Update server heartbeat timestamp.
     */
    public function updateHeartbeat(int $gameId, string $serverId): void;

    /**
     * Get servers that haven't sent heartbeat within timeout.
     *
     * @return array<string>
     */
    public function getStaleServers(int $gameId, int $timeoutSeconds): array;

    /**
     * Invalidate all cached data for a game.
     */
    public function invalidateGame(int $gameId): void;

    /**
     * Get the Redis key prefix for a game.
     */
    public function getGameKeyPrefix(int $gameId): string;
}

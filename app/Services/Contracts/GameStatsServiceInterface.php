<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\GameInstance;

interface GameStatsServiceInterface
{
    /**
     * Get current stats for a game instance (cached for 1 minute).
     *
     * @return array{active_servers: int, current_players: int, peak_players: int}
     */
    public function getCurrentStats(GameInstance $instance): array;

    /**
     * Get hourly stats for the last 24 hours.
     *
     * @return array<int, array{hour: string, active_servers: int, peak_players: int}>
     */
    public function getHourlyStats(GameInstance $instance, int $hours = 24): array;

    /**
     * Record server heartbeat data.
     */
    public function recordHeartbeat(GameInstance $instance, string $serverId, array $serverData): void;

    /**
     * Remove stale servers (no heartbeat in last 60 seconds).
     */
    public function removeStaleServers(GameInstance $instance): int;

    /**
     * Record hourly stats snapshot.
     */
    public function recordHourlySnapshot(GameInstance $instance): void;
}

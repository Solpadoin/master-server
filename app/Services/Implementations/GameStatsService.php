<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\Models\GameInstance;
use App\Services\Contracts\GameStatsServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class GameStatsService implements GameStatsServiceInterface
{
    private const HEARTBEAT_TTL = 60; // Servers considered stale after 60 seconds
    private const STATS_CACHE_TTL = 60; // Cache stats for 1 minute
    private const HOURLY_STATS_TTL = 86400; // Keep hourly stats for 24 hours

    public function getCurrentStats(GameInstance $instance): array
    {
        $cacheKey = "game_stats:{$instance->steam_app_id}:current";

        return Cache::remember($cacheKey, self::STATS_CACHE_TTL, function () use ($instance) {
            return $this->calculateCurrentStats($instance);
        });
    }

    public function getHourlyStats(GameInstance $instance, int $hours = 24): array
    {
        $stats = [];
        $now = now();

        for ($i = $hours - 1; $i >= 0; $i--) {
            $hour = $now->copy()->subHours($i)->format('Y-m-d-H');
            $key = $instance->getStatsRedisKey($hour);

            $data = Redis::hgetall($key);

            $stats[] = [
                'hour' => $now->copy()->subHours($i)->format('H:00'),
                'timestamp' => $now->copy()->subHours($i)->startOfHour()->toIso8601String(),
                'active_servers' => (int) ($data['active_servers'] ?? 0),
                'peak_players' => (int) ($data['peak_players'] ?? 0),
            ];
        }

        return $stats;
    }

    public function recordHeartbeat(GameInstance $instance, string $serverId, array $serverData): void
    {
        $key = $instance->getServersRedisKey();

        $serverData['last_heartbeat'] = now()->timestamp;
        $serverData['server_id'] = $serverId;

        Redis::hset($key, $serverId, json_encode($serverData));

        // Clear the stats cache so next request gets fresh data
        Cache::forget("game_stats:{$instance->steam_app_id}:current");
    }

    public function removeStaleServers(GameInstance $instance): int
    {
        $key = $instance->getServersRedisKey();
        $servers = Redis::hgetall($key);
        $staleThreshold = now()->timestamp - self::HEARTBEAT_TTL;
        $removed = 0;

        foreach ($servers as $serverId => $serverJson) {
            $server = json_decode($serverJson, true);

            if (($server['last_heartbeat'] ?? 0) < $staleThreshold) {
                Redis::hdel($key, $serverId);
                $removed++;
            }
        }

        if ($removed > 0) {
            Cache::forget("game_stats:{$instance->steam_app_id}:current");
        }

        return $removed;
    }

    public function recordHourlySnapshot(GameInstance $instance): void
    {
        $stats = $this->calculateCurrentStats($instance);
        $hour = now()->format('Y-m-d-H');
        $key = $instance->getStatsRedisKey($hour);

        // Get existing peak for this hour
        $existingPeak = (int) Redis::hget($key, 'peak_players');

        Redis::hmset($key, [
            'active_servers' => $stats['active_servers'],
            'peak_players' => max($existingPeak, $stats['current_players']),
            'recorded_at' => now()->toIso8601String(),
        ]);

        Redis::expire($key, self::HOURLY_STATS_TTL);
    }

    /**
     * Calculate current stats from Redis server data.
     */
    private function calculateCurrentStats(GameInstance $instance): array
    {
        $key = $instance->getServersRedisKey();
        $servers = Redis::hgetall($key);
        $staleThreshold = now()->timestamp - self::HEARTBEAT_TTL;

        $activeServers = 0;
        $currentPlayers = 0;
        $peakPlayers = 0;

        foreach ($servers as $serverJson) {
            $server = json_decode($serverJson, true);

            // Skip stale servers
            if (($server['last_heartbeat'] ?? 0) < $staleThreshold) {
                continue;
            }

            $activeServers++;
            $playerCount = (int) ($server['player_count'] ?? 0);
            $currentPlayers += $playerCount;
            $peakPlayers = max($peakPlayers, (int) ($server['peak_players'] ?? $playerCount));
        }

        return [
            'active_servers' => $activeServers,
            'current_players' => $currentPlayers,
            'peak_players' => $peakPlayers,
        ];
    }
}

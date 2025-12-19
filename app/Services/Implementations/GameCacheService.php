<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\DTOs\GameServerDTO;
use App\Services\Contracts\GameCacheServiceInterface;
use Illuminate\Support\Facades\Redis;

class GameCacheService implements GameCacheServiceInterface
{
    private const KEY_PREFIX = 'game';
    private const SERVER_KEY = 'servers';
    private const HEARTBEAT_KEY = 'heartbeats';

    public function getServers(int $gameId): ?array
    {
        $key = $this->getServersHashKey($gameId);
        $servers = Redis::hgetall($key);

        if (empty($servers)) {
            return null;
        }

        return array_map(
            fn (string $json) => GameServerDTO::fromArray(json_decode($json, true)),
            $servers
        );
    }

    public function getServer(int $gameId, string $serverId): ?GameServerDTO
    {
        $key = $this->getServersHashKey($gameId);
        $serverJson = Redis::hget($key, $serverId);

        if (! $serverJson) {
            return null;
        }

        return GameServerDTO::fromArray(json_decode($serverJson, true));
    }

    public function setServer(int $gameId, GameServerDTO $server): void
    {
        $key = $this->getServersHashKey($gameId);
        Redis::hset($key, $server->serverId, json_encode($server->toArray()));

        $this->updateHeartbeat($gameId, $server->serverId);
    }

    public function removeServer(int $gameId, string $serverId): void
    {
        $serversKey = $this->getServersHashKey($gameId);
        $heartbeatKey = $this->getHeartbeatHashKey($gameId);

        Redis::hdel($serversKey, $serverId);
        Redis::hdel($heartbeatKey, $serverId);
    }

    public function updateHeartbeat(int $gameId, string $serverId): void
    {
        $key = $this->getHeartbeatHashKey($gameId);
        Redis::hset($key, $serverId, (string) time());
    }

    public function getStaleServers(int $gameId, int $timeoutSeconds): array
    {
        $key = $this->getHeartbeatHashKey($gameId);
        $heartbeats = Redis::hgetall($key);

        $threshold = time() - $timeoutSeconds;
        $staleServers = [];

        foreach ($heartbeats as $serverId => $timestamp) {
            if ((int) $timestamp < $threshold) {
                $staleServers[] = $serverId;
            }
        }

        return $staleServers;
    }

    public function invalidateGame(int $gameId): void
    {
        $pattern = $this->getGameKeyPrefix($gameId) . ':*';
        $keys = Redis::keys($pattern);

        if (! empty($keys)) {
            Redis::del($keys);
        }
    }

    public function getGameKeyPrefix(int $gameId): string
    {
        return self::KEY_PREFIX . ':' . $gameId;
    }

    private function getServersHashKey(int $gameId): string
    {
        return $this->getGameKeyPrefix($gameId) . ':' . self::SERVER_KEY;
    }

    private function getHeartbeatHashKey(int $gameId): string
    {
        return $this->getGameKeyPrefix($gameId) . ':' . self::HEARTBEAT_KEY;
    }
}

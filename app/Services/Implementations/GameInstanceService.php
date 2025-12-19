<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\Models\GameInstance;
use App\Services\Contracts\GameInstanceServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class GameInstanceService implements GameInstanceServiceInterface
{
    public function getAll(): Collection
    {
        return GameInstance::all();
    }

    public function getById(int $id): ?GameInstance
    {
        return GameInstance::find($id);
    }

    public function getBySteamAppId(int $steamAppId): ?GameInstance
    {
        return GameInstance::where('steam_app_id', $steamAppId)->first();
    }

    public function create(int $steamAppId, string $name, int $memoryLimitMb = 1): GameInstance
    {
        $instance = GameInstance::create([
            'steam_app_id' => $steamAppId,
            'name' => $name,
            'memory_limit_mb' => min($memoryLimitMb, 10), // Max 10MB
            'is_active' => true,
        ]);

        $this->configureRedisMemory($instance);

        return $instance;
    }

    public function update(GameInstance $instance, array $data): GameInstance
    {
        if (isset($data['memory_limit_mb'])) {
            $data['memory_limit_mb'] = min($data['memory_limit_mb'], 10);
        }

        $instance->update($data);

        if (isset($data['memory_limit_mb'])) {
            $this->configureRedisMemory($instance);
        }

        return $instance->fresh();
    }

    public function delete(GameInstance $instance): bool
    {
        // Clean up Redis data for this instance
        $this->cleanupRedisData($instance);

        return $instance->delete();
    }

    public function configureRedisMemory(GameInstance $instance): void
    {
        // Note: Redis doesn't support per-key memory limits natively.
        // We track the limit in the database and enforce it in the application layer.
        // For actual memory management, we'd use Redis MEMORY commands or LRU eviction.
    }

    /**
     * Clean up all Redis data for a game instance.
     */
    private function cleanupRedisData(GameInstance $instance): void
    {
        $prefix = $instance->getRedisKeyPrefix();
        $keys = Redis::keys("{$prefix}:*");

        if (! empty($keys)) {
            // Remove the prefix that Redis adds
            $keysToDelete = array_map(function ($key) {
                // Handle potential prefix from Redis connection
                return preg_replace('/^[^:]+:/', '', $key) ?: $key;
            }, $keys);

            Redis::del($keysToDelete);
        }
    }
}

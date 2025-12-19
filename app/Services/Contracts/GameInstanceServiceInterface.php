<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\GameInstance;
use Illuminate\Support\Collection;

interface GameInstanceServiceInterface
{
    /**
     * Get all game instances.
     */
    public function getAll(): Collection;

    /**
     * Get a game instance by ID.
     */
    public function getById(int $id): ?GameInstance;

    /**
     * Get a game instance by Steam App ID.
     */
    public function getBySteamAppId(int $steamAppId): ?GameInstance;

    /**
     * Create a new game instance.
     */
    public function create(int $steamAppId, string $name, int $memoryLimitMb = 1): GameInstance;

    /**
     * Update a game instance.
     */
    public function update(GameInstance $instance, array $data): GameInstance;

    /**
     * Delete a game instance.
     */
    public function delete(GameInstance $instance): bool;

    /**
     * Configure Redis memory limit for the instance.
     */
    public function configureRedisMemory(GameInstance $instance): void;
}

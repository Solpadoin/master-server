<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ApiKey;
use Illuminate\Support\Collection;

interface ApiKeyRepositoryInterface
{
    /**
     * Find API key by key string.
     */
    public function findByKey(string $key): ?ApiKey;

    /**
     * Find active API key by key string.
     */
    public function findActiveByKey(string $key): ?ApiKey;

    /**
     * Get all API keys for a game.
     *
     * @return Collection<int, ApiKey>
     */
    public function findByGameId(int $gameId): Collection;

    /**
     * Create a new API key.
     */
    public function create(array $data): ApiKey;

    /**
     * Revoke an API key.
     */
    public function revoke(ApiKey $apiKey): bool;
}

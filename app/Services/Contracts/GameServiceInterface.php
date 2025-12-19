<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Game;
use App\Models\ApiKey;
use Illuminate\Support\Collection;

interface GameServiceInterface
{
    /**
     * Get all games.
     *
     * @return Collection<int, Game>
     */
    public function getAllGames(): Collection;

    /**
     * Get active games only.
     *
     * @return Collection<int, Game>
     */
    public function getActiveGames(): Collection;

    /**
     * Find game by ID.
     */
    public function findById(int $id): ?Game;

    /**
     * Find game by Steam App ID.
     */
    public function findBySteamAppId(int $steamAppId): ?Game;

    /**
     * Create a new game.
     */
    public function create(array $data): Game;

    /**
     * Update a game.
     */
    public function update(Game $game, array $data): Game;

    /**
     * Delete a game.
     */
    public function delete(Game $game): bool;

    /**
     * Generate API key for a game.
     *
     * @return array{api_key: ApiKey, plain_secret: string}
     */
    public function generateApiKey(Game $game, string $name): array;

    /**
     * Revoke an API key.
     */
    public function revokeApiKey(ApiKey $apiKey): bool;
}

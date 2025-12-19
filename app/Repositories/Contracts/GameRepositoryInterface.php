<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Game;
use Illuminate\Support\Collection;

interface GameRepositoryInterface
{
    /**
     * Get all games.
     *
     * @return Collection<int, Game>
     */
    public function all(): Collection;

    /**
     * Get active games only.
     *
     * @return Collection<int, Game>
     */
    public function getActive(): Collection;

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
}

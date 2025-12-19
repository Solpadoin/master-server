<?php

declare(strict_types=1);

namespace App\Repositories\Implementations;

use App\Models\Game;
use App\Repositories\Contracts\GameRepositoryInterface;
use Illuminate\Support\Collection;

class GameRepository implements GameRepositoryInterface
{
    public function all(): Collection
    {
        return Game::with(['instances', 'apiKeys'])->get();
    }

    public function getActive(): Collection
    {
        return Game::active()->with(['instances'])->get();
    }

    public function findById(int $id): ?Game
    {
        return Game::with(['instances', 'apiKeys'])->find($id);
    }

    public function findBySteamAppId(int $steamAppId): ?Game
    {
        return Game::where('steam_app_id', $steamAppId)
            ->with(['instances'])
            ->first();
    }

    public function create(array $data): Game
    {
        return Game::create($data);
    }

    public function update(Game $game, array $data): Game
    {
        $game->update($data);

        return $game->fresh(['instances', 'apiKeys']);
    }

    public function delete(Game $game): bool
    {
        return $game->delete();
    }
}

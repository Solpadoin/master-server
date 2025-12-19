<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\Models\Game;
use App\Models\ApiKey;
use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\ApiKeyRepositoryInterface;
use App\Services\Contracts\GameServiceInterface;
use Illuminate\Support\Collection;

class GameService implements GameServiceInterface
{
    public function __construct(
        private readonly GameRepositoryInterface $gameRepository,
        private readonly ApiKeyRepositoryInterface $apiKeyRepository,
    ) {}

    public function getAllGames(): Collection
    {
        return $this->gameRepository->all();
    }

    public function getActiveGames(): Collection
    {
        return $this->gameRepository->getActive();
    }

    public function findById(int $id): ?Game
    {
        return $this->gameRepository->findById($id);
    }

    public function findBySteamAppId(int $steamAppId): ?Game
    {
        return $this->gameRepository->findBySteamAppId($steamAppId);
    }

    public function create(array $data): Game
    {
        return $this->gameRepository->create($data);
    }

    public function update(Game $game, array $data): Game
    {
        return $this->gameRepository->update($game, $data);
    }

    public function delete(Game $game): bool
    {
        return $this->gameRepository->delete($game);
    }

    public function generateApiKey(Game $game, string $name): array
    {
        $keyPair = ApiKey::generateKeyPair();

        $apiKey = $this->apiKeyRepository->create([
            'game_id' => $game->id,
            'name' => $name,
            'key' => $keyPair['key'],
            'secret' => $keyPair['secret'],
        ]);

        return [
            'api_key' => $apiKey,
            'plain_secret' => $keyPair['secret'],
        ];
    }

    public function revokeApiKey(ApiKey $apiKey): bool
    {
        return $this->apiKeyRepository->revoke($apiKey);
    }
}

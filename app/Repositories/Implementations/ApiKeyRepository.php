<?php

declare(strict_types=1);

namespace App\Repositories\Implementations;

use App\Models\ApiKey;
use App\Repositories\Contracts\ApiKeyRepositoryInterface;
use Illuminate\Support\Collection;

class ApiKeyRepository implements ApiKeyRepositoryInterface
{
    public function findByKey(string $key): ?ApiKey
    {
        return ApiKey::where('key', $key)->with(['game'])->first();
    }

    public function findActiveByKey(string $key): ?ApiKey
    {
        return ApiKey::where('key', $key)
            ->active()
            ->with(['game'])
            ->first();
    }

    public function findByGameId(int $gameId): Collection
    {
        return ApiKey::where('game_id', $gameId)->get();
    }

    public function create(array $data): ApiKey
    {
        $apiKey = ApiKey::create($data);

        return $apiKey->load('game');
    }

    public function revoke(ApiKey $apiKey): bool
    {
        return $apiKey->update(['is_active' => false]);
    }
}

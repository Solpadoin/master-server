<?php

declare(strict_types=1);

namespace App\Repositories\Implementations;

use App\Repositories\Contracts\GameServerRepositoryInterface;

/**
 * Game servers are stored in Redis, not in MySQL.
 * This is a placeholder implementation.
 *
 * @see \App\Services\Implementations\GameCacheService
 */
class GameServerRepository implements GameServerRepositoryInterface
{
    // Game server data is managed through GameCacheService.
}

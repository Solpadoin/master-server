<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Note: Game servers are stored in Redis, not in MySQL.
 * This interface is kept for potential future database persistence.
 */
interface GameServerRepositoryInterface
{
    // Currently, game server data is managed through GameCacheService.
    // This interface is a placeholder for future implementation if needed.
}

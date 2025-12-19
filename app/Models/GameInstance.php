<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'steam_app_id',
        'name',
        'memory_limit_mb',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'steam_app_id' => 'integer',
            'memory_limit_mb' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the Redis key prefix for this game instance.
     */
    public function getRedisKeyPrefix(): string
    {
        return "game:{$this->steam_app_id}";
    }

    /**
     * Get the Redis key for servers data.
     */
    public function getServersRedisKey(): string
    {
        return "{$this->getRedisKeyPrefix()}:servers";
    }

    /**
     * Get the Redis key for hourly stats.
     */
    public function getStatsRedisKey(string $hour): string
    {
        return "{$this->getRedisKeyPrefix()}:stats:hourly:{$hour}";
    }
}

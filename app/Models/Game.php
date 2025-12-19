<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'steam_app_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'steam_app_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the instances for this game.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(Instance::class);
    }

    /**
     * Get the API keys for this game.
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Scope to only active games.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

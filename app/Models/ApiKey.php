<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'name',
        'key',
        'secret',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
    ];

    /**
     * Get the game that owns this API key.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Generate a new API key pair.
     */
    public static function generateKeyPair(): array
    {
        return [
            'key' => 'ms_' . Str::random(32),
            'secret' => Str::random(64),
        ];
    }

    /**
     * Mark the key as used.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope to only active keys.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

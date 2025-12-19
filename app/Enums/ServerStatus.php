<?php

declare(strict_types=1);

namespace App\Enums;

enum ServerStatus: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';
    case STARTING = 'starting';
    case FULL = 'full';

    public function label(): string
    {
        return match ($this) {
            self::ONLINE => 'Online',
            self::OFFLINE => 'Offline',
            self::STARTING => 'Starting',
            self::FULL => 'Full',
        };
    }

    public function isAvailable(): bool
    {
        return $this === self::ONLINE;
    }
}

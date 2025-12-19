<?php

declare(strict_types=1);

namespace App\Enums;

enum ServerType: string
{
    case LISTEN = 'listen';
    case DEDICATED = 'dedicated';

    public function label(): string
    {
        return match ($this) {
            self::LISTEN => 'Listen Server',
            self::DEDICATED => 'Dedicated Server',
        };
    }
}

<?php

namespace App\Enums;

enum WorkModel: string
{
    case ONSITE = 'onsite';
    case HYBRID = 'hybrid';
    case REMOTE = 'remote';

    public function label(): string
    {
        return match ($this) {
            self::ONSITE => 'Onsite',
            self::HYBRID => 'Hybrid',
            self::REMOTE => 'Remote',
        };
    }
}

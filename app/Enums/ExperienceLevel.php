<?php

namespace App\Enums;

enum ExperienceLevel: string
{
    case ENTRY = 'entry';
    case MID = 'mid';
    case SENIOR = 'senior';
    case LEAD = 'lead';

    public function label(): string
    {
        return match ($this) {
            self::ENTRY => 'Entry',
            self::MID => 'Mid',
            self::SENIOR => 'Senior',
            self::LEAD => 'Lead',
        };
    }
}

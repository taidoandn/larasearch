<?php

namespace App\Enums;

enum JobType: string
{
    case FULL_TIME = 'full-time';
    case CONTRACT = 'contract';
    case INTERNSHIP = 'internship';

    public function label(): string
    {
        return match ($this) {
            self::FULL_TIME => 'Full Time',
            self::CONTRACT => 'Contract',
            self::INTERNSHIP => 'Internship',
        };
    }
}

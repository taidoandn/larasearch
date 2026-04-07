<?php

namespace App\Enums;

enum JobType: string
{
    case FULL_TIME = 'full-time';
    case CONTRACT = 'contract';
    case INTERNSHIP = 'internship';
}

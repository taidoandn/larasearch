<?php

namespace App\Enums;

enum JobListingSourceType: string
{
    case DIRECT = 'direct';
    case IMPORTED = 'imported';
}

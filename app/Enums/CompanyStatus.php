<?php

namespace App\Enums;

enum CompanyStatus: string
{
    case ACTIVE = 'active';
    case HIDDEN = 'hidden';
    case SUSPENDED = 'suspended';
}

<?php

namespace App\Enums;

enum WorkModel: string
{
    case ONSITE = 'onsite';
    case HYBRID = 'hybrid';
    case REMOTE = 'remote';
}

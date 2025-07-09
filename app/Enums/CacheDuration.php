<?php

namespace App\Enums;

enum CacheDuration:int
{
    case SHORT = 60;
    case MEDIUM = 300;
    case LONG = 3600;
    case VERY_LONG = 86400;
}

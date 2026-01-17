<?php

namespace App\Enums;

interface Status
{
    const ACTIVE   = 5;
    const INACTIVE = 10;
    const CANCELED = 15;
    const UNDER_MAINTENANCE = 16;
    const BANNED = 17;
}

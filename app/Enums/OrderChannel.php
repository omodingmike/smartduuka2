<?php

namespace App\Enums;

enum OrderChannel: int
{
    case POS_TERMINAL = 1;
    case DIRECT_ORDER = 2;

    public function label(): string
    {
        return match ($this) {
            self::POS_TERMINAL => 'POS Terminal',
            self::DIRECT_ORDER => 'Direct Order',
        };
    }
}

<?php

namespace App\Enums;

use JsonSerializable;

enum PrintMode: int implements JsonSerializable
{
    case THERMAL = 1;
    case A4 = 2;
    case BOTH = 3;

    public function label(): string
    {
        return match ($this) {
            self::THERMAL => 'Thermal',
            self::A4 => 'A4',
            self::BOTH => 'Both',
        };
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
        ];
    }
}

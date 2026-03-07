<?php

namespace App\Enums;

use JsonSerializable;

enum PosPaymentType: int implements JsonSerializable
{
    case DEBT = 1;

    public function label(): string
    {
        return match ($this) {
            self::DEBT => 'DEBT',
        };
    }

    public static function options(): array
    {
        return array_map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ], self::cases());
    }

    public function jsonSerialize(): array
    {
        return [
            'label' => $this->label(),
            'value' => $this->value,
        ];
    }
}

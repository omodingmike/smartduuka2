<?php

namespace App\Enums;

use JsonSerializable;

enum ExpensePaymentStatus: int implements JsonSerializable
{
    case PAID = 5;
    case PARTIAL = 15;
    case UNPAID = 10;

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::PARTIAL => 'Partial',
            self::UNPAID => 'Unpaid',
        };
    }

    public function jsonSerialize(): array
    {
        return [
            'label' => $this->label(),
            'value' => $this->value,
        ];
    }
}

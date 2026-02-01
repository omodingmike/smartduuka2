<?php

namespace App\Enums;

use JsonSerializable;

enum PurchaseRequestStatus: int implements JsonSerializable
{
    case PENDING = 1;
    case APPROVED = 2;
    case REJECTED = 3;
    case ORDERED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::ORDERED => 'Ordered',
        };
    }

    public function jsonSerialize(): mixed
    {
        return [
            'label' => $this->label(),
            'value' => $this->value,
        ];
    }
}

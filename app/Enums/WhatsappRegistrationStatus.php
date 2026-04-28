<?php

namespace App\Enums;

use JsonSerializable;

enum WhatsappRegistrationStatus: int implements JsonSerializable
{
    case IDLE = 1;
    case PENDING = 2;
    case OTP = 3;
    case REGISTERED = 4;

    public function label(): string
    {
        return match ($this) {
            self::IDLE => 'Idle',
            self::PENDING => 'Pending',
            self::OTP => 'OTP',
            self::REGISTERED => 'Registered',
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

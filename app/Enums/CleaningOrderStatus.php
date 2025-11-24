<?php

    namespace App\Enums;

    enum CleaningOrderStatus : int
    {
        case PendingAcceptance = 1;
        case AwaitingDropOff   = 2;
        case AwaitingPickup    = 3;
        case Received          = 4;
        case Cleaning          = 5;
        case ReadyForPickup    = 6;
        case ReadyForDelivery  = 7;
        case Completed         = 8;
        case Cancelled         = 9;

        public function label() : string
        {
            return match ($this) {
                self::PendingAcceptance => 'Pending Acceptance',
                self::AwaitingDropOff   => 'Awaiting Drop-off',
                self::AwaitingPickup    => 'Awaiting Pickup',
                self::Received          => 'Received',
                self::Cleaning          => 'Cleaning',
                self::ReadyForPickup    => 'Ready for Pickup',
                self::ReadyForDelivery  => 'Ready for Delivery',
                self::Completed         => 'Completed',
                self::Cancelled         => 'Cancelled',
            };
        }

        public function color(): string
        {
            return match ($this) {
                self::PendingAcceptance => 'bg-indigo-500 hover:bg-indigo-600 text-white',
                self::AwaitingDropOff   => 'bg-sky-500 hover:bg-sky-600 text-white',
                self::AwaitingPickup    => 'bg-sky-500 hover:bg-sky-600 text-white',
                self::Received          => 'bg-primary hover:bg-primary/90 text-white',
                self::Cleaning          => 'bg-amber-500 hover:bg-amber-600 text-white',
                self::ReadyForPickup    => 'bg-constructive hover:bg-constructive/90 text-white',
                self::ReadyForDelivery  => 'bg-constructive hover:bg-constructive/90 text-white',
                self::Completed         => 'bg-emerald-600 hover:bg-emerald-700 text-white',
                self::Cancelled         => 'bg-rose-600 hover:bg-rose-700 text-white',
            };
        }
        public static function options(): array
        {
            return array_map(
                fn($case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ],
                self::cases()
            );
        }
    }

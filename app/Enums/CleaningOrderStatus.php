<?php

    namespace App\Enums;

    enum CleaningOrderStatus : int
    {
        case PendingAcceptance = 1;
        case AwaitingDropOff   = 3;
        case AwaitingPickup    = 4;
        case Received          = 5;
        case Cleaning          = 6;
        case ReadyForPickup    = 7;
        case ReadyForDelivery  = 8;
        case Completed         = 9;
        case Cancelled         = 10;
        case Accepted          = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::PendingAcceptance => 'Pending Acceptance' ,
                self::AwaitingDropOff   => 'Awaiting Drop off' ,
                self::AwaitingPickup    => 'Awaiting Pickup' ,
                self::Received          => 'Received' ,
                self::Cleaning          => 'Cleaning' ,
                self::ReadyForPickup    => 'Ready to Pickup' ,
                self::ReadyForDelivery  => 'Ready for Delivery' ,
                self::Completed         => 'Completed' ,
                self::Cancelled         => 'Cancelled' ,
                self::Accepted          => 'Accepted' ,
            };
        }

        public static function tryFromLabel(string $label) : ?self
        {
            foreach ( self::cases() as $case ) {
                if ( $case->label() === $label ) {
                    return $case;
                }
            }
            return NULL;
        }

        public static function options() : array
        {
            return array_map(
                fn($case) => [
                    'value' => $case->value ,
                    'label' => $case->label() ,
                ] ,
                self::cases()
            );
        }
    }

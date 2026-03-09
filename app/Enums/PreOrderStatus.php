<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PreOrderStatus : int implements JsonSerializable
    {
        case PENDING_STOCK    = 1;
        case READY_FOR_PICKUP = 2;
        case FULFILLED        = 3;
        case CANCELED         = 4;
        case REFUNDED         = 5;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING_STOCK    => 'Pending Stock' ,
                self::READY_FOR_PICKUP => 'Ready for Pickup' ,
                self::FULFILLED        => 'Fulfilled' ,
                self::CANCELED         => 'Canceled' ,
                self::REFUNDED         => 'Refunded' ,
            };
        }

        public function jsonSerialize() : mixed
        {
            return [
                'value' => $this->value ,
                'label' => $this->label() ,
            ];
        }
    }

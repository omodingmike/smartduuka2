<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PurchaseStatus : int implements JsonSerializable
    {
        case PENDING  = 1;
        case ORDERED  = 2;
        case RECEIVED = 3;
        case PARTIAL  = 4;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING  => 'Pending' ,
                self::ORDERED  => 'Ordered' ,
                self::RECEIVED => 'Received' ,
                self::PARTIAL  => 'Partial' ,
            };
        }

        public function jsonSerialize() : mixed
        {
            return [
                'label' => $this->label() ,
                'value' => $this->value ,
            ];
        }
    }

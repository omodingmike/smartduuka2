<?php

    namespace App\Enums;

    enum OrderType : int
    {
        case IN_STORE  = 1;
        case DELIVERY  = 2;
        case PICKUP    = 3;
        case ONLINE    = 4;
        case QUOTATION = 5;

        public function label() : string
        {
            return match ( $this ) {
                self::IN_STORE  => 'In Store' ,
                self::DELIVERY  => 'Delivery' ,
                self::PICKUP    => 'Pickup' ,
                self::ONLINE    => 'Online' ,
                self::QUOTATION => 'Quotation' ,
            };
        }

        public static function options() : array
        {
            return array_map( fn($type) => [
                'value' => $type->value ,
                'label' => $type->label() ,
            ] , self::cases() );
        }
    }

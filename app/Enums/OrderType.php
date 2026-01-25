<?php

    namespace App\Enums;

    enum OrderType : int
    {
        case DELIVERY  = 5;
        case PICK_UP   = 10;
        case POS       = 15;
        case CREDIT    = 20;
        case DEPOSIT   = 25;
        case QUOTATION = 30;
        case CASH      = 35;
        case COMPLETED = 40;

        public function label() : string
        {
            return match ( $this ) {
                self::DELIVERY  => 'Delivery' ,
                self::PICK_UP   => 'Pick Up' ,
                self::POS       => 'POS' ,
                self::CREDIT    => 'Credit' ,
                self::DEPOSIT   => 'Deposit' ,
                self::QUOTATION => 'Quotation' ,
                self::CASH      => 'Cash' ,
                self::COMPLETED => 'Completed' ,
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

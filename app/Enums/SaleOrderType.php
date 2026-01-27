<?php

    namespace App\Enums;

    enum SaleOrderType : int
    {
        case CREDIT = 20;
        case DEPOSIT = 25;
        case COMPLETED = 40;


        public function label() : string
        {
            return match ( $this ) {
                self::CREDIT    => 'Credit' ,
                self::DEPOSIT   => 'Deposit' ,
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

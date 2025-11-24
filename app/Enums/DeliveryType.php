<?php

    namespace App\Enums;

    enum DeliveryType : int
    {
        case CUSTOMER_DROPS_OFF_AND_COLLECTS = 0;
        case WE_PICK_UP_AND_DELIVER          = 1;
        case WE_PICK_UP_CUSTOMER_COLLECTS    = 2;
        case CUSTOMER_DROPS_OFF_WE_DELIVER   = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::CUSTOMER_DROPS_OFF_AND_COLLECTS => 'Customer Drops Off & Collects' ,
                self::WE_PICK_UP_AND_DELIVER          => 'We Pick Up & Deliver' ,
                self::WE_PICK_UP_CUSTOMER_COLLECTS    => 'We Pick Up, Customer Collects' ,
                self::CUSTOMER_DROPS_OFF_WE_DELIVER   => 'Customer Drops Off, We Deliver' ,
            };
        }

        public static function options() : array
        {
            return array_map(
                fn($case) => [ 'value' => $case->value , 'label' => $case->label() ] ,
                self::cases()
            );
        }
    }


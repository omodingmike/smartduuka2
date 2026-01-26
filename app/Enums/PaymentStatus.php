<?php

    namespace App\Enums;

    enum PaymentStatus : int
    {
        case PAID           = 5;
        case UNPAID         = 10;
        case PARTIALLY_PAID = 15;

        public function label() : string
        {
            return match ( $this ) {
                self::PAID           => 'Paid' ,
                self::UNPAID         => 'Unpaid' ,
                self::PARTIALLY_PAID => 'Partially Paid' ,
            };
        }
    }

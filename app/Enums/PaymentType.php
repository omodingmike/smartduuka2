<?php

    namespace App\Enums;

    enum PaymentType : int
    {
        case CREDIT   = 1;
        case DEPOSIT  = 2;
        case CASH     = 3;
        case PREORDER = 4;

        public function label() : string
        {
            return match ( $this ) {
                self::CREDIT   => 'Credit' ,
                self::DEPOSIT  => 'Deposit' ,
                self::CASH     => 'Cash' ,
                self::PREORDER => 'Pre-Order' ,
            };
        }
    }

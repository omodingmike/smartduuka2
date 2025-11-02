<?php

    namespace App\Enums;

    enum EnumDistributionStockStatusEnum : int
    {
        case OUTSTANDING = 5;
        case COMPLETED   = 10;

        public function label() : string
        {
            return match ( $this ) {
                self::OUTSTANDING => 'Outstanding' ,
                self::COMPLETED   => 'Completed' ,
            };
        }
    }

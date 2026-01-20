<?php

    namespace App\Enums;

    enum StockStatus : int
    {
        case OUT_OF_STOCK       = 1;
        case LOW_STOCK          = 2;
        case IN_STOCK           = 3;
        case IN_TRANSIT         = 4;
        case RECEIVED           = 5;
        case APPROVED           = 6;
        case PARTIALLY_RECEIVED = 7;
        case REJECTED           = 8;
        case PENDING            = 10;
        case CANCELED           = 15;
        case EXPIRED            = 20;

        public function label() : string
        {
            return match ( $this ) {
                self::OUT_OF_STOCK       => 'Out of Stock' ,
                self::LOW_STOCK          => 'Low Stock' ,
                self::IN_STOCK           => 'In Stock' ,
                self::IN_TRANSIT         => 'In Transit' ,
                self::RECEIVED           => 'Received' , //Only increment for this case
                self::APPROVED           => 'Approved' ,
                self::PARTIALLY_RECEIVED => 'Partially Received' ,
                self::REJECTED           => 'Rejected' ,
                self::PENDING            => 'Pending' ,
                self::CANCELED           => 'Canceled' ,
                self::EXPIRED            => 'Expired' ,
            };
        }

        public static function options() : array
        {
            return array_map( fn($status) => [
                'value' => $status->value ,
                'label' => $status->label() ,
            ] , self::cases() );
        }
    }
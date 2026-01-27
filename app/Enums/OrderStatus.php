<?php

    namespace App\Enums;

    enum OrderStatus : int
    {
        case PENDING          = 1;
        case CONFIRMED        = 2;
        case ON_THE_WAY       = 3;
        case DELIVERED        = 4;
        case CANCELED         = 5;
        case REJECTED         = 6;
        case ACCEPT           = 7;
        case RETURNED         = 8;
        case COMPLETED        = 9;
        case APPROVED         = 10;
        case DISPATCHED       = 11;
        case PROCESSING       = 12;
        case OUT_FOR_DELIVERY = 13;
        case READY_FOR_PICKUP = 14;
        public function label() : string
        {
            return match ( $this ) {
                self::PENDING          => 'Pending' ,
                self::CONFIRMED        => 'Confirmed' ,
                self::ON_THE_WAY       => 'On The Way' ,
                self::DELIVERED        => 'Delivered' ,
                self::CANCELED         => 'Canceled' ,
                self::REJECTED         => 'Rejected' ,
                self::ACCEPT           => 'Accept' ,
                self::RETURNED         => 'Returned' ,
                self::COMPLETED        => 'Completed' ,
                self::APPROVED         => 'Approved' ,
                self::DISPATCHED       => 'Dispatched' ,
                self::PROCESSING       => 'Processing' ,
                self::OUT_FOR_DELIVERY => 'Out For Delivery' ,
                self::READY_FOR_PICKUP => 'Ready For Pickup' ,
            };
        }
    }

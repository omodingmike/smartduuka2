<?php

    namespace App\Enums;

    interface OrderStatus
    {
        const PENDING          = 1;
        const CONFIRMED        = 5;
        const ON_THE_WAY       = 7;
        const DELIVERED        = 10;
        const CANCELED         = 15;
        const REJECTED         = 20;
        const ACCEPT           = 4;
        const PROCESSING       = 7;
        const OUT_FOR_DELIVERY = 10;
        const RETURNED         = 22;
        const PREPARED         = 23;
        const COMPLETED        = 24;
        const APPROVED         = 25;
    }

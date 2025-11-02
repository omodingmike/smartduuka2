<?php

    namespace App\Enums;

    interface StockStatus
    {
        const RECEIVED = 5;
        const PENDING  = 10;
        const CANCELED = 15;
        const EXPIRED  = 20;
    }

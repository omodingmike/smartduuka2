<?php

    namespace App\Enums;

    interface StockStatus
    {
        const RECEIVED     = 5;
        const PENDING      = 10;
        const CANCELED     = 15;
        const EXPIRED      = 20;
        const OUT_OF_STOCK = 1;
        const LOW_STOCK    = 2;
        const IN_STOCK     = 3;
    }

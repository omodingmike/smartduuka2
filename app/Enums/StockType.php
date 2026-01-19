<?php

    namespace App\Enums;

    interface StockType
    {
        const TRANSFER       = 2; //transfer
        const REQUESTS       = 3; //requests
        const DISTRIBUTION   = 4; //distribution
        const RECONCILIATION = 5; //reconciliation
    }

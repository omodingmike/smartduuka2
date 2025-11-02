<?php

    namespace App\Enums;

    interface OrderType
    {
        const DELIVERY  = 5;
        const PICK_UP   = 10;
        const POS       = 15;
        const CREDIT    = 20;
        const DEPOSIT   = 25;
        public const QUOTATION = 30;
        const CASH = 10;
    }

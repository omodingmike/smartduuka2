<?php

    namespace App\Enums;

    interface PaymentMethodEnum
    {
        public const TAKE_AWAY = 10;
        public const CREDIT    = 20;
        public const DEPOSIT   = 25;
        public const QUOTATION = 30;
    }

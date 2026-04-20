<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PaymentMethodEnum : int implements JsonSerializable
    {
        case TAKE_AWAY = 10;
        case CREDIT    = 20;
        case DEPOSIT   = 25;
        case QUOTATION = 30;

        public function label() : string
        {
            return match ( $this ) {
                self::TAKE_AWAY => 'Take Away' ,
                self::CREDIT    => 'Credit' ,
                self::DEPOSIT   => 'Deposit' ,
                self::QUOTATION => 'Quotation' ,
            };
        }

        public function jsonSerialize() : array
        {
            return [
                'value' => $this->value ,
                'label' => $this->label() ,
            ];
        }
    }

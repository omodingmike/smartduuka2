<?php

    namespace App\Enums;

    use JsonSerializable;

    enum SubAccountType : int implements JsonSerializable
    {
        case BANK_ACCOUNT = 1;
        case MOBILE_MONEY = 2;
        case CASH_IN_HAND = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::BANK_ACCOUNT => 'Bank Account' ,
                self::MOBILE_MONEY => 'Mobile Money' ,
                self::CASH_IN_HAND => 'Cash in Hand' ,
            };
        }

        public function jsonSerialize() : mixed
        {
            return [
                'value' => $this->value ,
                'label' => $this->label() ,
            ];
        }
    }

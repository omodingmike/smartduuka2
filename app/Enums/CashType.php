<?php

    namespace App\Enums;

    use JsonSerializable;

    enum CashType : int implements JsonSerializable
    {
        case CASH_IN  = 1;
        case CASH_OUT = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::CASH_IN  => 'Cash In' ,
                self::CASH_OUT => 'Cash Out' ,
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

<?php

    namespace App\Enums;

    use JsonSerializable;

    enum CustomerPaymentType : int implements JsonSerializable
    {
        case DEBT     = 1;
        case WITHDRAW = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::DEBT     => 'Debt' ,
                self::WITHDRAW => 'Withdraw' ,
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

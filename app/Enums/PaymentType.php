<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PaymentType : int implements JsonSerializable
    {
        case CREDIT   = 1;
        case DEPOSIT  = 2;
        case CASH     = 3;
        case PREORDER = 4;

        public function label() : string
        {
            return match ( $this ) {
                self::CREDIT   => 'Credit' ,
                self::DEPOSIT  => 'Deposit' ,
                self::CASH     => 'Cash' ,
                self::PREORDER => 'Pre-Order' ,
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

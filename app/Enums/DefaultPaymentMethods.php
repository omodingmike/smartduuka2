<?php

    namespace App\Enums;

    use JsonSerializable;

    enum DefaultPaymentMethods : string implements JsonSerializable
    {
        case CASH   = 'Cash';
        case WALLET = 'Wallet';

        public function label() : string
        {
            return match ( $this ) {
                self::CASH   => 'Cash' ,
                self::WALLET => 'Wallet' ,
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

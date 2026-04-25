<?php

    namespace App\Enums;

    use JsonSerializable;

    enum QuotationType : int implements JsonSerializable
    {
        case PRODUCT = 1;
        case SERVICE = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::PRODUCT => 'Product' ,
                self::SERVICE => 'Service' ,
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

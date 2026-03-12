<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PriceType : int implements JsonSerializable
    {
        case RETAIL    = 1;
        case WHOLESALE = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::RETAIL    => 'Retail' ,
                self::WHOLESALE => 'Wholesale' ,
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

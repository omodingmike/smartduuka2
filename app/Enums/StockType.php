<?php

    namespace App\Enums;

    use JsonSerializable;

    enum StockType : int implements JsonSerializable
    {
        case PURCHASE       = 1;
        case TRANSFER       = 2;
        case REQUESTS       = 3;
        case DISTRIBUTION   = 4;
        case RECONCILIATION = 5;

        public function label() : string
        {
            return match ( $this ) {
                self::PURCHASE       => 'Purchase' ,
                self::TRANSFER       => 'Transfer' ,
                self::REQUESTS       => 'Requests' ,
                self::DISTRIBUTION   => 'Distribution' ,
                self::RECONCILIATION => 'Reconciliation' ,
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

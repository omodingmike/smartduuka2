<?php

    namespace App\Enums;

    use JsonSerializable;

    enum StockReconciliationType : int implements JsonSerializable
    {
        case SELLABLE         = 1;
        case RESERVED         = 2;
        case MOVE_TO_SELLABLE = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::SELLABLE         => 'Count Sellable Stock' ,
                self::RESERVED         => 'Count Reserved Stock' ,
                self::MOVE_TO_SELLABLE => 'Release to Sellable' ,
            };
        }

        public static function options() : array
        {
            return array_map( fn($type) => [
                'value' => $type->value ,
                'label' => $type->label() ,
            ] , self::cases() );
        }

        public function jsonSerialize() : array
        {
            return [
                'label' => $this->label() ,
                'value' => $this->value ,
            ];
        }
    }

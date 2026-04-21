<?php

    namespace App\Enums;

    use JsonSerializable;

    enum ServiceType : int implements JsonSerializable
    {
        case STANDARD = 1;
        case PACKAGE  = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::STANDARD => 'Standard' ,
                self::PACKAGE  => 'Package' ,
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

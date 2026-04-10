<?php

    namespace App\Enums;

    use JsonSerializable;

    enum EntityType : int implements JsonSerializable
    {
        case CLIENT   = 1;
        case VENDOR   = 2;
        case INTERNAL = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::CLIENT   => 'Client / Customer' ,
                self::VENDOR   => 'Vendor / Supplier' ,
                self::INTERNAL => 'Internal / General' ,
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

<?php

    namespace App\Enums;

    use JsonSerializable;

    enum ReturnType : int implements JsonSerializable
    {
        case RESELLABLE = 1;
        case DAMAGED    = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::RESELLABLE => 'Resellable' ,
                self::DAMAGED    => 'Damaged' ,
            };
        }

        public function jsonSerialize() : array
        {
            return [
                'label' => $this->label() ,
                'value' => $this->value ,
            ];
        }
    }

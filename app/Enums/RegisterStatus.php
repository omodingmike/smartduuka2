<?php

    namespace App\Enums;

    use JsonSerializable;

    enum RegisterStatus : int implements JsonSerializable
    {
        case OPEN   = 1;
        case CLOSED = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::OPEN   => 'Open' ,
                self::CLOSED => 'Closed' ,
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

<?php

    namespace App\Enums;

    use JsonSerializable;

    enum Priority : int implements JsonSerializable
    {
        case LOW    = 1;
        case MEDIUM = 2;
        case HIGH   = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::LOW    => 'Low' ,
                self::MEDIUM => 'Medium' ,
                self::HIGH   => 'High' ,
            };
        }

        public function jsonSerialize() : mixed
        {
            return [
                'label' => $this->label() ,
                'value' => $this->value ,
            ];
        }
    }

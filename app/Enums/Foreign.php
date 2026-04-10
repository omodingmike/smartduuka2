<?php

    namespace App\Enums;

    use JsonSerializable;

    enum Foreign : int implements JsonSerializable
    {
        case YES = 1;
        case NO  = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::YES => 'Yes' ,
                self::NO  => 'No' ,
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

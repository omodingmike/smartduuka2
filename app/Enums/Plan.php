<?php

    namespace App\Enums;

    use JsonSerializable;

    enum Plan : int implements JsonSerializable
    {
        case STARTER = 1;
        case PRO     = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::STARTER => 'Starter Plan' ,
                self::PRO     => 'Pro Plan' ,
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

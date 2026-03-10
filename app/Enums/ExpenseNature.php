<?php

    namespace App\Enums;

    use JsonSerializable;

    enum ExpenseNature : int implements JsonSerializable
    {
        case OPERATIONAL     = 1;
        case NON_OPERATIONAL = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::OPERATIONAL     => 'Operational' ,
                self::NON_OPERATIONAL => 'Non-Operational' ,
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

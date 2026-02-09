<?php

    namespace App\Enums;

    use JsonSerializable;

    enum ExpenseType : int implements JsonSerializable
    {
        case NON_RECURRING   = 1;
        case RECURRING       = 2;
        case SYSTEM_CAPTURED = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::NON_RECURRING   => 'Non-Recurring' ,
                self::RECURRING       => 'Recurring' ,
                self::SYSTEM_CAPTURED => 'System Captured' ,
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

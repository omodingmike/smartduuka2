<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PlanStatus : int implements JsonSerializable
    {
        case ACTIVE    = 1;
        case CANCELLED = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::ACTIVE    => 'Active' ,
                self::CANCELLED => 'Cancelled' ,
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

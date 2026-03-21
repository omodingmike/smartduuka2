<?php

    namespace App\Enums;

    use JsonSerializable;

    enum ReturnStatus : int implements JsonSerializable
    {
        case PENDING   = 1;
        case APPROVED  = 2;
        case REJECTED  = 3;
        case COMPLETED = 4;
        case CANCELED  = 5;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING   => 'Pending' ,
                self::APPROVED  => 'Approved' ,
                self::REJECTED  => 'Rejected' ,
                self::COMPLETED => 'Completed' ,
                self::CANCELED  => 'Canceled' ,
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

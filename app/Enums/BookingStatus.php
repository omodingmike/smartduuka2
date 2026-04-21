<?php

    namespace App\Enums;

    use JsonSerializable;

    enum BookingStatus : int implements JsonSerializable
    {
        case PENDING     = 1;
        case IN_PROGRESS = 2;
        case COMPLETED   = 3;
        case CANCELLED   = 4;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING     => 'Pending' ,
                self::IN_PROGRESS => 'In-Progress' ,
                self::COMPLETED   => 'Completed' ,
                self::CANCELLED   => 'Cancelled' ,
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

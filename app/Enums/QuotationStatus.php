<?php

    namespace App\Enums;

    use JsonSerializable;

    enum QuotationStatus : int implements JsonSerializable
    {
        case PENDING   = 1;
        case APPROVED  = 2;
        case CONVERTED = 3;
        case EXPIRED   = 4;
        case ACCEPTED  = 5;
        case REJECTED  = 6;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING   => 'Pending' ,
                self::APPROVED  => 'Approved' ,
                self::CONVERTED => 'Converted' ,
                self::EXPIRED   => 'Expired' ,
                self::ACCEPTED  => 'Accepted' ,
                self::REJECTED  => 'Rejected' ,
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

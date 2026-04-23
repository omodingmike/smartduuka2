<?php

    namespace App\Enums;

    use JsonSerializable;

    enum QuotationStatus : int implements JsonSerializable
    {
        case PENDING       = 1;
        case APPROVED      = 2;
        case CONVERTED     = 3;
        case EXPIRED       = 4;
        case ACCEPTED      = 5;
        case CANCELLED     = 6;
        case COUNTER_OFFER = 7;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING       => 'Pending' ,
                self::APPROVED      => 'Approved' ,
                self::CONVERTED     => 'Converted' ,
                self::EXPIRED       => 'Expired' ,
                self::ACCEPTED      => 'Accepted' ,
                self::CANCELLED     => 'Cancelled' ,
                self::COUNTER_OFFER => 'COUNTER OFFER' ,
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

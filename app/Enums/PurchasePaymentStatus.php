<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PurchasePaymentStatus : int implements JsonSerializable
    {
        case PENDING      = 5;
        case PARTIAL_PAID = 10;
        case FULLY_PAID   = 15;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING      => 'Pending' ,
                self::PARTIAL_PAID => 'Partial Paid' ,
                self::FULLY_PAID   => 'Fully Paid' ,
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

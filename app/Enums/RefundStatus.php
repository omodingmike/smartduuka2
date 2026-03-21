<?php

    namespace App\Enums;

    use JsonSerializable;

    enum RefundStatus : int implements JsonSerializable
    {
        case PENDING        = 1;
        case PARTIAL        = 2;
        case REFUNDED       = 3;
        case NOT_APPLICABLE = 4;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING        => 'Pending Refund' ,
                self::PARTIAL        => 'Partially Refunded' ,
                self::REFUNDED       => 'Refunded' ,
                self::NOT_APPLICABLE => 'N/A' ,
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

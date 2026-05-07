<?php

    namespace App\Enums;

    use JsonSerializable;

    enum BillingCycle : int implements JsonSerializable
    {
        case MONTHLY     = 1;
        case QUARTERLY   = 2;
        case HALF_YEARLY = 3;
        case YEARLY      = 4;

        public function label() : string
        {
            return match ( $this ) {
                self::MONTHLY     => 'Monthly' ,
                self::QUARTERLY   => 'Quarterly' ,
                self::HALF_YEARLY => 'Half-Yearly' ,
                self::YEARLY      => 'Yearly' ,
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

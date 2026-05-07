<?php

    namespace App\Enums;

    use JsonSerializable;

    enum SubscriptionPaymentStatus : int implements JsonSerializable
    {
        case Paid    = 1;
        case Pending = 2;
        case Failed  = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::Paid    => 'Paid' ,
                self::Pending => 'Pending' ,
                self::Failed  => 'Failed' ,
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

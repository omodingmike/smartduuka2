<?php

    namespace App\Enums;

    use JsonSerializable;

    enum SubscriptionPlanType : int implements JsonSerializable
    {
        case Starter  = 1;
        case Existing = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::Starter  => 'Starter' ,
                self::Existing => 'Existing' ,
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

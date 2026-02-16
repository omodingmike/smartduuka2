<?php

    namespace App\Enums;

    use ArchTech\Enums\InvokableCases;
    use JsonSerializable;

    enum Status : int implements JsonSerializable
    {
        use InvokableCases;

        case ACTIVE            = 5;
        case INACTIVE          = 10;
        case CANCELED          = 15;
        case UNDER_MAINTENANCE = 16;
        case BANNED            = 17;
        case ON_LEAVE          = 20;
        case TERMINATED        = 25;

        public function label() : string
        {
            return match ( $this ) {
                self::ACTIVE            => 'Active' ,
                self::INACTIVE          => 'Inactive' ,
                self::CANCELED          => 'Canceled' ,
                self::UNDER_MAINTENANCE => 'Under Maintenance' ,
                self::BANNED            => 'Banned' ,
                self::ON_LEAVE          => 'On Leave' ,
                self::TERMINATED        => 'Terminated' ,
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

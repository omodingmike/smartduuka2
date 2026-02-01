<?php

    namespace App\Enums;

    use JsonSerializable;

    enum Department : int implements JsonSerializable
    {
        case SALES_FLOOR = 1;
        case WAREHOUSE   = 2;
        case ADMIN       = 3;
        case SECURITY    = 4;
        case MAINTENANCE = 5;

        public function label() : string
        {
            return match ( $this ) {
                self::SALES_FLOOR => 'Sales Floor' ,
                self::WAREHOUSE   => 'Warehouse' ,
                self::ADMIN       => 'Admin' ,
                self::SECURITY    => 'Security' ,
                self::MAINTENANCE => 'Maintenance' ,
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

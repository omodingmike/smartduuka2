<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PrintJobType : string implements JsonSerializable
    {
        case RAW    = 'raw';
        case HTML   = 'html';
        case DRAWER = 'drawer';

        public function label() : string
        {
            return match ( $this ) {
                self::RAW    => 'Raw' ,
                self::HTML   => 'Html' ,
                self::DRAWER => 'Drawer' ,
            };
        }

        public function jsonSerialize() : mixed
        {
            return [
                'value' => $this->value ,
                'label' => $this->label() ,
            ];
        }
    }

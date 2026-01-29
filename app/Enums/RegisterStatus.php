<?php

namespace App\Enums;

enum RegisterStatus : int
{
    case OPEN   = 1;
    case CLOSED = 2;

    public function label() : string
    {
        return match ( $this ) {
            self::OPEN   => 'Open' ,
            self::CLOSED => 'Closed' ,
        };
    }

    public static function options() : array
    {
        return array_map( fn($status) => [
            'value' => $status->value ,
            'label' => $status->label() ,
        ] , self::cases() );
    }
}

<?php

    namespace App\Enums;

    enum DamageStatus : string
    {
        case Pending  = 'pending';
        case Approved = 'approved';
        case Rejected = 'rejected';

        public function label() : string
        {
            return match ( $this ) {
                self::Pending  => 'Pending' ,
                self::Approved => 'Approved' ,
                self::Rejected => 'Rejected' ,
            };
        }

        public static function values() : array
        {
            return array_map(
                static fn(self $status) => $status->value ,
                self::cases()
            );
        }

        public static function labels() : array
        {
            return array_map(
                static fn(self $status) => $status->label() ,
                self::cases()
            );
        }
    }

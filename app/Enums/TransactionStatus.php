<?php

    namespace App\Enums;

    use JsonSerializable;

    enum TransactionStatus : int implements JsonSerializable
    {
        case CLEARED  = 1;
        case APPROVED = 2;
        case DRAFT    = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::CLEARED  => 'Cleared (Funds Settled)' ,
                self::APPROVED => 'Approved (Awaiting Clearing)' ,
                self::DRAFT    => 'Draft (Needs Approval)' ,
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

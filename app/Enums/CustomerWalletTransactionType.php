<?php

    namespace App\Enums;

    use JsonSerializable;

    enum CustomerWalletTransactionType : int implements JsonSerializable
    {
        case PURCHASE = 1;
        case DEPOSIT  = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::PURCHASE => 'Purchase' ,
                self::DEPOSIT  => 'Deposit' ,
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

<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PurchaseType : int implements JsonSerializable
    {
        case STOCK_PURCHASE = 1;
        case EXPENSE        = 2;
        case ASSET_PURCHASE = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::STOCK_PURCHASE => 'Stock Purchase' ,
                self::EXPENSE        => 'Expense' ,
                self::ASSET_PURCHASE => 'Asset Purchase' ,
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

<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use Illuminate\Http\Resources\Json\JsonResource;

    class IngredientResource extends JsonResource
    {
        public function toArray($request) : array
        {
            return [
                "id"                    => $this->id ,
                "name"                  => $this->name ,
                "buying_price"          => $this->buying_price ,
                "buying_price_currency" => AppLibrary::currencyAmountFormat($this->buying_price) ,
                "unit"                  => $this->unit ,
                "quantity"              => number_format($this->quantity,2) ,
                "quantity_alert"        => number_format($this->quantity_alert,2) ,
                "pivot"                 => $this->pivot ,
            ];
        }
    }

<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class MeatPriceResource extends JsonResource
    {
        public function toArray($request) : array
        {
            return [
                "adults"         => $this->adults ,
                "five_to_nine"   => $this->five_to_nine,
                "less_than_five" => $this->less_than_five,
            ];
        }
    }

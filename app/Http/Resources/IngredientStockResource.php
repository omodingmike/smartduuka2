<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class IngredientStockResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
//                'id'             => $this->id ,
//                'product_id'     => $this->product_id ,
//                'price'          => $this->price ,
//                'status'         => $this->status ,
//                'price_currency' => AppLibrary::currencyAmountFormat($this->price) ,
//                'quantity'       => number_format($this->quantity,2) ,
//                'item'           => $this->ingredient ,


                'name'           => $this['name'] ,
                'quantity'       => $this['quantity'] ,
                'quantity_alert' => $this['quantity_alert'] ,
                'unit'           => $this['unit'] ,
                'status'         => $this['status'] ,
            ];
        }
    }

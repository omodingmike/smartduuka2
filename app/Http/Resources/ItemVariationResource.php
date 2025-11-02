<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ItemVariationResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                'id'                    => $this->id ,
                'product_id'               => $this->product_id ,
                'item_attribute_id'     => $this->item_attribute_id ,
                'name'                  => $this->name ,
                'price'                 => $this->price ,
                "flat_price"            => AppLibrary::flatAmountFormat($this->price) ,
                "convert_price"         => AppLibrary::convertAmountFormat($this->price) ,
                "currency_price"        => AppLibrary::currencyAmountFormat($this->price) ,
                'caution'               => $this->caution ,
                'status'                => $this->status ,
                'item'                  => optional($this->item)->name ,
                'item_attribute'        => optional($this->itemAttribute)->name ,
                'ingredients'           => $this->ingredients ,
                'overall_cost'          => $this->overall_cost ,
                'overall_cost_currency' => AppLibrary::currencyAmountFormat($this->overall_cost) ,
            ];
        }
    }

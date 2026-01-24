<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class RetailPriceResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request) : array
        {
            return [
                'id'                 => $this->id ,
                'unit_id'            => $this->unit_id ,
                'buying_price'       => $this->buying_price ,
                'buying_price_text'  => AppLibrary::currencyAmountFormat( $this->buying_price ) ,
                'selling_price'      => $this->selling_price ,
                'selling_price_text' => AppLibrary::currencyAmountFormat( $this->selling_price ) ,
            ];
        }
    }

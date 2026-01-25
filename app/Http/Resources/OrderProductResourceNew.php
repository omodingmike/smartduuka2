<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\OrderProduct;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin OrderProduct */
    class OrderProductResourceNew extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                  => $this->id ,
                'quantity'            => $this->quantity ,
                'quantity_text'       => number_format( $this->quantity ) ,
                'total'               => $this->total ,
                'item'                => $this->item ,
                'total_currency'      => AppLibrary::currencyAmountFormat( $this->total ) ,
                'unit_price'          => $this->unit_price ,
                'unit_price_currency' => AppLibrary::currencyAmountFormat( $this->unit_price ) ,
            ];
        }
    }

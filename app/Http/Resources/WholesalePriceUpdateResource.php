<?php

    namespace App\Http\Resources;

    use App\Models\WholesalePriceUpdate;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin WholesalePriceUpdate */
    class WholesalePriceUpdateResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                 => $this->id ,
                'min_quantity'       => $this->min_quantity ,
                'old_price'          => $this->old_price ,
                'old_price_currency' => currency( $this->old_price ) ,
                'new_price'          => $this->new_price ,
                'new_price_currency' => currency( $this->new_price ) ,
                'item_id'            => $this->item_id ,
                'item_type'          => $this->item_type ,
                'created_at'         => datetime( $this->created_at ) ,
                'updated_at'         => datetime( $this->updated_at ) ,
            ];
        }
    }

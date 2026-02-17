<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\RetailPriceUpdate;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin RetailPriceUpdate */
    class RetailPriceUpdateResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                 => $this->id ,
                'old_price'          => $this->old_price ,
                'old_price_currency' => currency( $this->old_price ) ,
                'new_price'          => $this->new_price ,
                'new_price_currency' => currency( $this->new_price ) ,
                'created_at'         => AppLibrary::datetime2( $this->created_at ) ,
                'updated_at'         => AppLibrary::datetime2( $this->updated_at ) ,
                'unit_id'            => $this->unit_id ,
                'unit'               => new UnitResource( $this->unit ) ,
//                'item_id'    => $this->item_id ,
//                'item_type'  => $this->item_type ,
                'item'               => $this->item_type === 'App\Models\Product'
                    ? new SimpleProductResource( $this->item )
                    : new ProductVariationResource( $this->item ) ,
            ];
        }
    }

<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\WholeSalePrice;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin WholeSalePrice */
    class WholeSalePriceResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => rand() ,
                'minQuantity' => $this->minQuantity ,
                'price'       => $this->price ,
                'price_text'  => AppLibrary::currencyAmountFormat( $this->price ) ,
                'item_id'     => $this->item_id ,
                'product_id'  => $this->item_id ,
                'item_type'   => $this->item_type ,
            ];
        }
    }

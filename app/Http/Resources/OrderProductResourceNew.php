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
            $is_variation = $this->variation_id;
            return [
                'id'                          => $this->id ,
                'quantity'                    => $this->quantity ,
                'quantity_picked'             => $this->quantity_picked ,
                'product_attribute_id'        => $this->product_attribute_id ,
                'product_attribute_option_id' => $this->product_attribute_option_id ,
                'quantity_text'               => number_format( $this->quantity ) ,
                'quantity_picked_text'        => number_format( $this->quantity_picked ) ,
                'total'                       => $this->total ,
                'item'                        => $is_variation ? new ProductVariationResource( $this->item ) : new SimpleProductDetailsResource( $this->item ) ,
                'total_currency'              => AppLibrary::currencyAmountFormat( $this->total ) ,
                'unit_price'                  => $this->unit_price ,
                'unit_price_currency'         => AppLibrary::currencyAmountFormat( $this->unit_price ) ,
            ];
        }
    }

<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\OrderProduct;
    use App\Models\RetailPrice;
    use App\Models\WholeSalePrice;
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
                'quantity'                    => (float) $this->quantity ,
                'is_variation'                => $this->is_variation ,
                'quantity_picked'             => (float) $this->quantity_picked ,
                'product_attribute_id'        => $this->product_attribute_id ,
                'price'                       => $this->price ? (
                $this->price instanceof RetailPrice ? [
//                    'buying_price'       => $this->price->buying_price ,
//                    'buying_price_text'  => AppLibrary::currencyAmountFormat( $this->price->buying_price ) ,
                    'selling_price'      => (int) $this->price->selling_price ,
                    'type'               => 1 ,
                    'id'                 => $this->price_id ,
                    'selling_price_text' => AppLibrary::currencyAmountFormat( $this->price->selling_price ) ,
                ] : ( $this->price instanceof WholeSalePrice
                    ? [
                        'selling_price'      => (int) $this->price->price ,
                        'type'               => 2 ,
                        'id'                 => $this->price_id ,
                        'selling_price_text' => AppLibrary::currencyAmountFormat( $this->price->price ) ,
                    ]
                    : $this->price
                )
                ) : NULL ,
                'product_attribute_option_id' => $this->product_attribute_option_id ,
                'quantity_text'               => number_format( $this->quantity ) ,
                'quantity_picked_text'        => number_format( $this->quantity_picked ) ,
                'total'                       => (float) $this->total ,
                'item'                        => $is_variation ? new ProductVariationResource( $this->item ) : new SimpleProductDetailsResource( $this->item ) ,
                'total_currency'              => AppLibrary::currencyAmountFormat( $this->total ) ,
                'unit_price'                  => (float) $this->unit_price ,
                'unit_price_currency'         => AppLibrary::currencyAmountFormat( $this->unit_price ) ,
            ];
        }
    }

<?php

    namespace App\Http\Resources;

    use App\Enums\PriceType;
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
            $price_data   = NULL;
            if ( $this->price ) {
                if ( $this->price instanceof RetailPrice ) {
                    $price_data = [
                        'selling_price'      => (int) $this->price->selling_price ,
                        'type'               => PriceType::RETAIL ,
                        'id'                 => $this->price_id ,
                        'selling_price_text' => AppLibrary::currencyAmountFormat( $this->price->selling_price ) ,
                    ];
                }
                elseif ( $this->price instanceof WholeSalePrice ) {
                    $price_data = [
                        'selling_price'      => (int) $this->price->price ,
                        'type'               => PriceType::WHOLESALE ,
                        'id'                 => $this->price_id ,
                        'selling_price_text' => AppLibrary::currencyAmountFormat( $this->price->price ) ,
                    ];
                }
            }
            return [
                'id'                          => $this->id ,
                'quantity'                    => (float) $this->quantity ,
                'is_variation'                => $this->is_variation ,
                'quantity_picked'             => (float) $this->quantity_picked ,
                'product_attribute_id'        => $this->product_attribute_id ,
                'price'                       => $price_data ,
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

<?php

    namespace App\Http\Resources;

    use App\Enums\PriceType;
    use App\Libraries\AppLibrary;
    use App\Models\OrderProduct;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\RetailPrice;
    use App\Models\Service;
    use App\Models\WholeSalePrice;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin OrderProduct */
    class OrderProductResourceNew extends JsonResource
    {

        public function toArray(Request $request) : array
        {
            $price_data = NULL;
            if ( $this->price && $this->item_type !== Service::class ) {
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

            $itemResource = NULL;
            if ( $this->item_type === ProductVariation::class ) {
                $itemResource = new ProductVariationResource( $this->whenLoaded( 'item' ) );
            }
            elseif ( $this->item_type === Product::class ) {
                $itemResource = new SimpleProductDetailsResource( $this->whenLoaded( 'item' ) );
            }
            elseif ( $this->item_type === Service::class ) {
                $itemResource = new ServiceResource( $this->whenLoaded( 'item' ) );
            }

            return [
                'id'                          => $this->id ,
                'quantity'                    => (float) $this->quantity ,
                'is_variation'                => $this->item_type === ProductVariation::class ,
                'is_return'                   => $this->is_return ,
                'is_exchange'                 => $this->is_exchange ,
                'quantity_picked'             => (float) $this->quantity_picked ,
                'return_quantity'             => (int) $this->return_quantity ,
                'product_attribute_id'        => $this->product_attribute_id ,
                'price'                       => $price_data ,
                'product_attribute_option_id' => $this->product_attribute_option_id ,
                'quantity_text'               => number_format( $this->quantity ) ,
                'quantity_picked_text'        => number_format( $this->quantity_picked ) ,
                'total'                       => (float) $this->total ,
                'item'                        => $itemResource ,
                'total_currency'              => AppLibrary::currencyAmountFormat( $this->total ) ,
                'unit_price'                  => (float) $this->unit_price ,
                'unit_price_currency'         => AppLibrary::currencyAmountFormat( $this->unit_price ) ,
                'quotation_item_type'         => $this->quotation_item_type ,
            ];
        }
    }

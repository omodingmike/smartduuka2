<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\ProductVariation;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ProductVariationResource extends JsonResource
    {
        /** @mixin ProductVariation */
        public function toArray($request) : array
        {
            return [
                'id'                            => $this->id ,
                'product_id'                    => $this->product_id ,
                'product_attribute_id'          => $this->product_attribute_id ,
                'product_attribute_option_id'   => $this->product_attribute_option_id ,
                'price'                         => AppLibrary::flatAmountFormat( $this->price ) ,
                'price_currency'                => currency( $this->price ) ,
                'sku'                           => $this->sku ,
                'parent_id'                     => $this->parent_id ,
                'order'                         => $this->order ,
                'wholesalePrices'               => WholeSalePriceResource::collection( $this->whenLoaded( 'wholesalePrices' ) ) ,
                'retailPrices'                  => RetailPriceResource::collection( $this->whenLoaded( 'retailPrices' ) ) ,
                'stock'                         => $this->stock ,
                'unit'                          => new UnitResource( $this->product->unit ) ,
                'product'                       => $this->product->name ,
                'product_attribute_name'        => $this->productAttribute->name ,
                'product_attribute_option_name' => $this->productAttributeOption->name ,
                'product_attribute'             => $this->productAttribute ,
                'product_attribute_option'      => $this->productAttributeOption
            ];
        }
    }

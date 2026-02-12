<?php

    namespace App\Http\Resources;


    use App\Enums\Activity;
    use App\Enums\Ask;
    use App\Libraries\AppLibrary;
    use App\Models\ProductVariation;
    use App\Models\Unit;
    use Carbon\Carbon;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ProductVariationResource extends JsonResource
    {
        /** @mixin ProductVariation */
        public function toArray($request) : array
        {
            // Assuming ProductVariation has similar relationships/attributes as Product where applicable
            // Or we map parent product attributes if variation doesn't override them.
            
            $product = $this->product; // Parent product
            
            // Price logic for variation
            $price = $this->price;
            
            // Offer logic (usually on parent product, but could be on variation if supported)
            // Assuming offer is on parent product for now
            $offerActive = $product->offer_start_date && $product->offer_end_date && Carbon::now()->between( $product->offer_start_date , $product->offer_end_date );
            
            $discountedPrice = $offerActive
                ? $price - ( ( $price / 100 ) * $product->discount )
                : $price;

            $stock = max(0, $this->stock); // Variation stock

            return [
                'id'                            => $this->id ,
                'product_id'                    => $this->product_id ,
                'name'                          => $product->name . ' (' . ($this->productAttributeOption->name ?? '') . ')', // Construct name
                'slug'                          => $product->slug , // Variation doesn't usually have slug, use parent
                'product_attribute_id'          => $this->product_attribute_id ,
                'product_attribute_option_id'   => $this->product_attribute_option_id ,
                
                'price'                         => AppLibrary::convertAmountFormat( $discountedPrice ) ,
                'currency_price'                => AppLibrary::currencyAmountFormat( AppLibrary::convertAmountFormat( $discountedPrice ) ) ,
                'old_price'                     => AppLibrary::convertAmountFormat( $price ) ,
                'old_currency_price'            => AppLibrary::currencyAmountFormat( $price ) ,
                'discount'                      => $offerActive ? AppLibrary::convertAmountFormat( ( $price / 100 ) * $product->discount ) : 0 ,
                'discount_percentage'           => AppLibrary::convertAmountFormat( $product->discount ) ,
                'flash_sale'                    => $product->add_to_flash_sale == Ask::YES ,
                'is_offer'                      => $offerActive ,
                
                'sku'                           => $this->sku ,
                'parent_id'                     => $this->parent_id ,
                'order'                         => $this->order ,
                
                'wholesalePrices'               => WholeSalePriceResource::collection( $this->whenLoaded( 'wholesalePrices' ) ) ,
                'retailPrices'                  => RetailPriceResource::collection( $this->whenLoaded( 'retailPrices' ) ) ,
                
                'stock'                         => (int) $stock ,
                'other_stock'                   => $product->show_stock_out == Activity::DISABLE
                                                    ? ( $product->can_purchasable == Ask::NO
                                                        ? (int) config( 'system.non_purchase_quantity' )
                                                        : (int) $stock )
                                                    : 0 ,
                                                    
                'unit'                          => new UnitResource( $product->unit ) ,
                'unit_id'                       => $product->unit_id,
                'code'                          => $product->unit?->code,
                
                'product'                       => $product->name ,
                'product_attribute_name'        => $this->productAttribute->name ?? null ,
                'product_attribute_option_name' => $this->productAttributeOption->name ?? null ,
                'product_attribute'             => $this->productAttribute ,
                'product_attribute_option'      => $this->productAttributeOption,
                
                'cover'                         => $this->cover ?? $product->cover, // Fallback to product cover if variation doesn't have one
                'thumb'                         => $this->thumb ?? $product->thumb,
                'image'                         => $this->preview ?? $product->preview,
                'images'                        => $this->previews ?? $product->previews, // Assuming previews attribute exists on variation too or fallback
                
                'rating_star'                   => $product->rating_star,
                'rating_star_count'             => $product->rating_star_count,
                
                'details'                       => $product->description, // Variation usually shares description
                'shipping_and_return'           => $product->shipping_and_return,
                'category_slug'                 => $product->category?->slug,
                
                // Unit conversions (inherited from product)
                'retail_unit_id'             => $product->retail_unit_id ,
                'retail_unit'                => $product->retail_unit_id ? new UnitResource( Unit::find( $product->retail_unit_id ) ) : NULL ,
                'mid_unit'                   => $product->mid_unit_id ? new UnitResource( Unit::find( $product->mid_unit_id ) ) : NULL ,
                'top_unit'                   => $product->top_unit_id ? new UnitResource( Unit::find( $product->top_unit_id ) ) : NULL ,
                'mid_unit_id'                => $product->mid_unit_id ,
                'top_unit_id'                => $product->top_unit_id ,
                'units_per_mid_unit'         => (float) $product->units_per_mid_unit ,
                'mid_units_per_top_unit'     => (float) $product->mid_units_per_top_unit ,
                'base_units_per_top_unit'    => (float) $product->base_units_per_top_unit ,
                'mid_unit_wholesale_price'   => (float) $product->mid_unit_wholesale_price ,
                'top_unit_wholesale_price'   => (float) $product->top_unit_wholesale_price ,
                'retail_price_per_base_unit' => (float) $product->retail_price_per_base_unit ,
                
                'shipping'                   => [
                    'shipping_type'                => $product->shipping_type ,
                    'shipping_cost'                => $product->shipping_cost ,
                    'is_product_quantity_multiply' => $product->is_product_quantity_multiply ,
                ] ,
            ];
        }
    }

<?php

    namespace App\Http\Resources;


    use App\Enums\Activity;
    use App\Enums\Ask;
    use App\Libraries\AppLibrary;
    use App\Models\Product;
    use App\Models\Unit;
    use Carbon\Carbon;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SimpleProductDetailsResource extends JsonResource
    {

        public function toArray($request) : array
        {
            $product                 = Product::find( $this->id );
            $base_units_per_top_unit = $product->base_units_per_top_unit;
            $units_per_mid_unit      = $product->units_per_mid_unit;
            $price                   = count( $this->variations ) > 0 ? $this->variation_price : $this->selling_price;
            $stock                   = max( 0 , $this->stock_items_sum_quantity );

            // Safely check if offer is active
            $offerActive = $this->offer_start_date && $this->offer_end_date && Carbon::now()->between( $this->offer_start_date , $this->offer_end_date );

            // Compute discounted price only if offer is active
            $discountedPrice = $offerActive
                ? $price - ( ( $price / 100 ) * $this->discount )
                : $price;

            return [
                'id'                         => $this->id ,
                'name'                       => $this->name ,
                'slug'                       => $this->slug ,
                'price'                      => AppLibrary::convertAmountFormat( $discountedPrice ) ,
                'currency_price'             => AppLibrary::currencyAmountFormat( AppLibrary::convertAmountFormat( $discountedPrice ) ) ,
                'old_price'                  => AppLibrary::convertAmountFormat( $price ) ,
                'old_currency_price'         => AppLibrary::currencyAmountFormat( $price ) ,
                'discount'                   => $offerActive ? AppLibrary::convertAmountFormat( ( $price / 100 ) * $this->discount ) : 0 ,
                'discount_percentage'        => AppLibrary::convertAmountFormat( $this->discount ) ,
                'flash_sale'                 => $this->add_to_flash_sale == Ask::YES ,
                'is_offer'                   => $offerActive ,
                'rating_star'                => $this->rating_star ,
                'rating_star_count'          => $this->rating_star_count ,
                'image'                      => $this->cover ,
                'images'                     => $this->previews ,
                'units_nature'               => $this->units_nature ,
                'taxes'                      => SimpleTaxResource::collection( $this->taxes ) ,
                'reviews'                    => ProductReviewResource::collection( $this->reviews ) ,
                'details'                    => $this->description ,
                'shipping_and_return'        => $this->shipping_and_return ,
                'category_slug'              => $this->category?->slug ,
                'unit'                       => $this->unit ? new UnitResource( $this->unit ) : NULL ,
                'stock_unit'                 => $this->unit?->code ,
                'prices'                     => $this->prices ,
                'retail_unit_id'             => $this->retail_unit_id ,
                'retail_unit'                => $this->retail_unit_id ? new UnitResource( Unit::find( $this->retail_unit_id ) ) : NULL ,
                'mid_unit'                   => $this->mid_unit_id ? new UnitResource( Unit::find( $this->mid_unit_id ) ) : NULL ,
                'top_unit'                   => $this->top_unit_id ? new UnitResource( Unit::find( $this->top_unit_id ) ) : NULL ,
                'mid_unit_id'                => $this->mid_unit_id ,
                'top_unit_id'                => $this->top_unit_id ,
                'units_per_mid_unit'         => (float) $this->units_per_mid_unit ,
                'mid_units_per_top_unit'     => (float) $this->mid_units_per_top_unit ,
                'base_units_per_top_unit'    => (float) $this->base_units_per_top_unit ,
                'mid_unit_wholesale_price'   => (float) $this->mid_unit_wholesale_price ,
                'top_unit_wholesale_price'   => (float) $this->top_unit_wholesale_price ,
                'retail_price_per_base_unit' => (float) $this->retail_price_per_base_unit ,
                'unit_id'                    => $this->unit_id ,
                'selling_units'              => UnitResource::collection( $this->sellingUnits ) ,
                'stock'                      => (int) $stock ?? 0 ,
                'top_stock'                  => $base_units_per_top_unit ? intdiv( $stock , $base_units_per_top_unit ) : $stock ,
                'mid_stock'                  => $units_per_mid_unit ? intdiv( $stock , $units_per_mid_unit ) : NULL ,
                'base_stock'                 => (int) $stock ,
                'other_stock'                => $this->show_stock_out == Activity::DISABLE
                    ? ( $this->can_purchasable == Ask::NO
                        ? (int) config( 'system.non_purchase_quantity' )
                        : (int) $stock )
                    : 0 ,
                'sku'                        => $this->sku ,
                'code'                       => $this->unit?->code ,
                'other_code'                 => $this->otherUnit?->code ,
                'shipping'                   => [
                    'shipping_type'                => $this->shipping_type ,
                    'shipping_cost'                => $this->shipping_cost ,
                    'is_product_quantity_multiply' => $this->is_product_quantity_multiply ,
                ] ,
            ];

        }
    }

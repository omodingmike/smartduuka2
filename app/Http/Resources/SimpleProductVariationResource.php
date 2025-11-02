<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use Carbon\Carbon;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SimpleProductVariationResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                'id'                            => $this->id ,
                'product_attribute_id'          => $this->product_attribute_id ,
                'product_attribute_option_id'   => $this->product_attribute_option_id ,
                'product_attribute_name'        => $this->productAttribute?->name ,
                'product_attribute_option_name' => $this->productAttributeOption?->name ,

                // --- Safely determine if product offer is active ---
                'price'                         => ( function () {
                    $product     = $this->product;
                    $offerActive = $product && $product->offer_start_date && $product->offer_end_date
                        ? Carbon::now()->between( $product->offer_start_date , $product->offer_end_date )
                        : FALSE;

                    $price           = $this->price;
                    $discountedPrice = $offerActive
                        ? $price - ( ( $price / 100 ) * ( $product->discount ?? 0 ) )
                        : $price;

                    return AppLibrary::convertAmountFormat( $discountedPrice );
                } )() ,

                'currency_price' => ( function () {
                    $product     = $this->product;
                    $offerActive = $product && $product->offer_start_date && $product->offer_end_date
                        ? Carbon::now()->between( $product->offer_start_date , $product->offer_end_date )
                        : FALSE;

                    $price           = $this->price;
                    $discountedPrice = $offerActive
                        ? $price - ( ( $price / 100 ) * ( $product->discount ?? 0 ) )
                        : $price;

                    return AppLibrary::currencyAmountFormat( $discountedPrice );
                } )() ,

                'old_price'          => AppLibrary::convertAmountFormat( $this->price ) ,
                'old_currency_price' => AppLibrary::currencyAmountFormat( $this->price ) ,

                'discount' => ( function () {
                    $product     = $this->product;
                    $offerActive = $product && $product->offer_start_date && $product->offer_end_date && Carbon::now()->between( $product->offer_start_date , $product->offer_end_date );

                    return $offerActive
                        ? AppLibrary::convertAmountFormat( ( $this->price / 100 ) * ( $product->discount ?? 0 ) )
                        : 0;
                } )() ,

                'discount_percentage' => AppLibrary::convertAmountFormat( $this->product?->discount ?? 0 ) ,
                'sku'                 => $this->sku ,
                'units_nature'        => $this->product?->units_nature ,
                'code'                => $this->product?->unit?->code ,
                'other_code'          => $this->product?->otherUnit?->code ,
                'stock'               => (int) $this->stock_items_sum_quantity ,
                'otherStock'          => (int) ( $this->other_stock_items_sum_other_quantity ?? 0 ) ,
            ];
        }
    }

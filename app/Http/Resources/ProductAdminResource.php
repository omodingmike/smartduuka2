<?php

    namespace App\Http\Resources;

    use App\Enums\PriceType;
    use App\Libraries\AppLibrary;
    use App\Models\Product;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin Product
     */
    class ProductAdminResource extends JsonResource
    {
        public function toArray($request) : array
        {
            $price = count( $this->variations ) > 0 ? $this->variation_price : $this->selling_price;

            $retail = $this->retailPrices->map( function ($pr) {
                return [
                    'price'      => $pr->selling_price ,
                    'price_text' => currency( $pr->selling_price ) ,
                    'id'         => $pr->id ,
                    'type'       => PriceType::RETAIL->value ,
                ];
            } );

            $wholesale = $this->wholesalePrices->map( function ($pw) {
                return [
                    'price'      => $pw->price ,
                    'price_text' => currency( $pw->price ) ,
                    'id'         => $pw->id ,
                    'type'       => PriceType::WHOLESALE->value ,
                ];
            } );

            $prices = $retail->merge( $wholesale )
                             ->unique( 'price' )
                             ->sortBy( 'price' )
                             ->values()
                             ->toArray();
            return [
                "id"                         => $this->id ,
                "name"                       => $this->name ,
                "sku"                        => $this->sku ,
                "type"                       => $this->type ,
                "unit"                       => $this->unit ,
                "barcode"                    => $this->barcode ,
                "deposited"                  => $this->deposited ,
                'approve_quantity'           => number_format( $this->approve_quantity ) ,
                'request_quantity'           => number_format( $this->request_quantity ) ,
                'quantity'                   => abs( $this->transfer_quantity ) ?? 0 ,
                'quantity_text'              => number_format( abs( $this->transfer_quantity ) ?? 0 ) ,
                "stock"                      => $this->stock ,
                "stock_text"                 => number_format( $this->stock ) ,
                "slug"                       => $this->slug ,
                "prices"                     => $prices ,
                "product_category_id"        => $this->product_category_id ,
                "barcode_id"                 => $this->barcode_id ,
                "product_brand_id"           => $this->product_brand_id ,
                "unit_id"                    => $this->unit_id ,
                "single_tree"                => $this->single_tree ,
                'taxes'                      => TaxResource::collection( $this->whenLoaded( 'taxes' , function () {
                    return $this->taxes->map->tax;
                } ) ) ,
                'tax_inclusive'              => $this->tax_inclusive ,
//                "variations"                 => ProductVariationResource::collection( $this->variations ) ,
                // Inside ProductAdminResource.php -> toArray()
                'variations'                 => $this->variations->map( function ($variation) {
                    // Build the options array specifically for this variation
                    $options = [];
                    $nodes   = $variation->ancestorsAndSelf()
                                         ->with( [ 'productAttribute' , 'productAttributeOption' ] )
                                         ->get()
                                         ->reverse();

                    foreach ( $nodes as $node ) {
                        if ( $node->productAttribute && $node->productAttributeOption ) {
                            $options[] = [
                                'attribute_name' => $node->productAttribute->name ,
                                'option_name'    => $node->productAttributeOption->name ,
                            ];
                        }
                    }

                    // Use the existing Resource but merge the new options array
                    return array_merge(
                        ( new ProductVariationResource( $variation ) )->toArray( request() ) ,
                        [ 'options' => $options ]
                    );
                } ) ,
                "wholesalePrices"            => WholeSalePriceResource::collection( $this->wholesalePrices ) ,
//                "wholesalePrices"            => WholeSalePriceResource::collection(
//                    $this->wholesalePrices->where( 'batch' , $latestBatch )
//                ) ,
//                'old_wholesale_prices'       => WholeSalePriceResource::collection(
//                    $this->wholesalePrices->where( 'batch' , $previousBatch ?? $latestBatch )
//                ) ,
                "retailPrices"               => RetailPriceResource::collection( collect( [ $this->retailPrices->first() ] ) ) ,
//                "old_retail_prices"          => RetailPriceResource::collection(
//                    $this->retailPrices->count() > 1 ? $this->retailPrices->skip( 1 )->take( 1 ) : $this->retailPrices->take( 1 )
//                ) ,
                "track_stock"                => $this->track_stock ,
                "returnable"                 => $this->returnable ,
                "weight_unit_id"             => $this->weight_unit_id ,
                "tax_id"                     => ProductTaxResource::collection( $this->taxes ) ,
                "flat_buying_price"          => AppLibrary::currencyAmountFormat( $this->buying_price ) ,
                "buying_price"               => $this->buying_price ,
                "flat_selling_price"         => AppLibrary::currencyAmountFormat( $this->selling_price ) ,
                "selling_price"              => $this->selling_price ,
                "status"                     => $this->status ,
                "status_text"                => $this->status ? 'Visible' : 'Hidden' ,
                "other_unit_id"              => $this->other_unit_id ,
                "can_purchasable"            => $this->can_purchasable ,
                "show_stock_out"             => $this->show_stock_out ,
                "maximum_purchase_quantity"  => $this->maximum_purchase_quantity ,
                "low_stock_quantity_warning" => $this->low_stock_quantity_warning ,
                "weight"                     => $this->weight ,
                "refundable"                 => $this->refundable ,
                "description"                => $this->description === NULL ? '' : $this->description ,
//                "tags"                       => $this->tags->pluck( 'name' )->implode( ',' ) ,
                "tags"                       => $this->tags->map( function ($tag) {
                    return [
                        'id'   => $tag->id ,
                        'name' => $tag->name ,
                    ];
                } ) ,
                "category_name"              => ucwords( $this?->category?->name ) ,
                "brand"                      => $this?->brand ,
                "order"                      => abs( $this?->productOrders->sum( 'quantity' ) ) ,
                'currency_price'             => AppLibrary::currencyAmountFormat( $price ) ,
                "cover"                      => $this->cover ,
                "thumb"                      => $this->thumb ,
                'image'                      => $this->preview ,
            ];
        }
    }

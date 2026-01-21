<?php

    namespace App\Http\Resources;

    use App\Enums\Ask;
    use App\Enums\StockStatus;
    use App\Libraries\AppLibrary;
    use App\Models\Product;
    use App\Models\Unit;
    use Carbon\Carbon;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ProductAdminResource extends JsonResource
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
            $product                 = Product::find( $this->id );
            $base_units_per_top_unit = $product->base_units_per_top_unit;
            $units_per_mid_unit      = $product->units_per_mid_unit;
            $price                   = count( $this->variations ) > 0 ? $this->variation_price : $this->selling_price;
            $stock                   = $this->stocks()->where( 'status' , StockStatus::RECEIVED )->sum( 'quantity' );
            return [
                "id"                         => $this->id ,
                "name"                       => $this->name ,
                "sku"                        => $this->sku ,
                "type"                       => $this->type ,
                "unit"                       => $this->unit ,
                "barcode"                    => $this->barcode ,
                "stock"                      => $stock ,
                "stock_text"                 => number_format( $stock ) ,
                "slug"                       => $this->slug ,
                "product_category_id"        => $this->product_category_id ,
                "barcode_id"                 => $this->barcode_id ,
                "product_brand_id"           => $this->product_brand_id ,
                'retail_unit_id'             => $this->retail_unit_id ,
                'retail_unit'                => $this->retail_unit_id ? new UnitResource( Unit::find( $this->retail_unit_id ) ) : NULL ,
                'mid_unit'                   => $this->retail_unit_id ? new UnitResource( Unit::find( $this->mid_unit_id ) ) : NULL ,
                'top_unit'                   => $this->top_unit_id ? new UnitResource( Unit::find( $this->top_unit_id ) ) : NULL ,
                'mid_unit_id'                => $this->mid_unit_id ,
                'top_unit_id'                => $this->top_unit_id ,
                'units_per_mid_unit'         => $this->units_per_mid_unit ,
                'mid_units_per_top_unit'     => $this->mid_units_per_top_unit ,
                'base_units_per_top_unit'    => $this->base_units_per_top_unit ,
                'mid_unit_wholesale_price'   => $this->mid_unit_wholesale_price ,
                'top_unit_wholesale_price'   => $this->top_unit_wholesale_price ,
                'retail_price_per_base_unit' => $this->retail_price_per_base_unit ,
                "unit_id"                    => $this->unit_id ,
                "units_nature"               => $this->units_nature ,
                "prices"                     => $this->prices->map( function ($price) {
                    return [
                        'id'         => $price->id ,
                        'product_id' => $price->product_id ,
                        'unit_id'    => $price->unit_id ,
                        'price'      => AppLibrary::currencyAmountFormat( $price->price ) ,
                        'unit'       => new UnitResource( $price->unit ) ,
                    ];
                } ) ,
                "retail_prices"              => $this->prices->filter( fn($price) => $price->type == 0 )->values() ,
                "wholesale_prices"           => $this->prices->filter( fn($price) => $price->type == 1 )->values() ,
                "other_units_detailed"       => UnitResource::collection( $this->sellingUnits ) ,
                "selling_units"              => UnitResource::collection( $this->sellingUnits ) ,
                "selling_units_flat"         => $this->sellingUnits->map( fn($unit) => [ $unit->id ] )->flatten() ,
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
                "product_tags"               => ProductTagResource::collection( $this->tags ) ,
                "category_name"              => ucwords( $this?->category?->name ) ,
                "brand"                      => $this?->brand?->name ,
                "order"                      => abs( $this?->productOrders->sum( 'quantity' ) ) ,
                'currency_price'             => AppLibrary::currencyAmountFormat( $price ) ,
                "cover"                      => $this->cover ,
                "thumb"                      => $this->thumb ,
                'preview'                    => $this->preview ,
                'image'                      => $this->preview ,
                'images'                     => $this->previews ,
                'flash_sale'                 => $this->add_to_flash_sale == Ask::YES ,
                'is_offer'                   => $this->offer_start_date && $this->offer_end_date && Carbon::now()->between( $this->offer_start_date , $this->offer_end_date ) ,
                'discounted_price'           => AppLibrary::currencyAmountFormat( $price - ( ( $price / 100 ) * $this->discount ) ) ,
                'rating_star'                => $this->rating_star ,
                'rating_star_count'          => $this->rating_star_count ,
                "barcode_image"              => $this->barcodeImage ,
            ];
        }
    }

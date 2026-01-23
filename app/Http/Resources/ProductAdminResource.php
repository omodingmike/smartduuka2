<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
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
            $price = count( $this->variations ) > 0 ? $this->variation_price : $this->selling_price;
            return [
                "id"                         => $this->id ,
                "name"                       => $this->name ,
                "sku"                        => $this->sku ,
                "type"                       => $this->type ,
                "unit"                       => $this->unit ,
                "barcode"                    => $this->barcode ,
                'quantity'                   => abs( $this->transfer_quantity ) ?? 0 ,
                'quantity_text'              => number_format( abs( $this->transfer_quantity ) ?? 0 ) ,
                "stock"                      => $this->stock ,
                "stock_text"                 => number_format( $this->stock ) ,
                "slug"                       => $this->slug ,
                "product_category_id"        => $this->product_category_id ,
                "barcode_id"                 => $this->barcode_id ,
                "product_brand_id"           => $this->product_brand_id ,
                "unit_id"                    => $this->unit_id ,
                "wholesalePrices"            => $this->wholesalePrices ,
                "retailPrices"               => $this->retailPrices ,
                "track_stock"                => $this->track_stock ,
                "returnable"                 => $this->returnable ,
                "weight_unit_id"             => $this->weight_unit_id ,
//                "prices"                     => $this->prices->map( function ($price) {
//                    return [
//                        'id'         => $price->id ,
//                        'product_id' => $price->product_id ,
//                        'unit_id'    => $price->unit_id ,
//                        'price'      => AppLibrary::currencyAmountFormat( $price->price ) ,
//                        'unit'       => new UnitResource( $price->unit ) ,
//                    ];
//                } ) ,
//                "retail_prices"              => $this->prices->filter( fn($price) => $price->type == 0 )->values() ,
//                "wholesale_prices"           => $this->prices->filter( fn($price) => $price->type == 1 )->values() ,
//                "other_units_detailed"       => UnitResource::collection( $this->sellingUnits ) ,
//                "selling_units"              => UnitResource::collection( $this->sellingUnits ) ,
//                "selling_units_flat"         => $this->sellingUnits->map( fn($unit) => [ $unit->id ] )->flatten() ,
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
                "tags"                       => $this->tags->pluck( 'name' )->implode( ',' ) ,
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

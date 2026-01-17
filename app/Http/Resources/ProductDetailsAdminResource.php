<?php

    namespace App\Http\Resources;


    use App\Enums\Activity;
    use App\Enums\Ask;
    use App\Libraries\AppLibrary;
    use Carbon\Carbon;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ProductDetailsAdminResource extends JsonResource
    {
        public function toArray($request) : array
        {
            $price = count($this?->variations) > 0 ? $this->variation_price : $this->selling_price;
            return [
                "id"                         => $this->id ,
                "name"                       => $this->name ,
                "sku"                        => $this->sku ,
                "category"                   => $this->category?->name ,
                "brand"                      => $this->brand?->name ,
//                "barcode"                    => $this->barcode?->name ,
                "user_barcode"               => $this->user_barcode ,
                "tax"                        => AppLibrary::taxString($this?->taxes) ,
                "flat_buying_price"          => AppLibrary::flatAmountFormat($this->buying_price) ,
                "flat_selling_price"         => AppLibrary::flatAmountFormat($this->selling_price) ,
                "maximum_purchase_quantity"  => $this->maximum_purchase_quantity ,
                "low_stock_quantity_warning" => $this->low_stock_quantity_warning ,
                "weight"                     => $this->weight ,
                "unit"                       => $this->unit?->name ,
                "can_purchasable"            => $this->can_purchasable ,
                "show_stock_out"             => $this->show_stock_out ,
                "refundable"                 => $this->refundable ,
                "units_nature"               => $this->units_nature ,
                "status"                     => $this->status ,
                "prices"                     => $this->prices ,
                "tags"                       => AppLibrary::tagString($this?->tags) ,
                "description"                => $this->description === null ? '' : $this->description ,
                "preview"                    => $this->preview ,
                "image"                      => $this->preview ,
                "images"                     => $this->previews ,
                "add_to_flash_sale"          => $this->add_to_flash_sale ,
                "offer_start_date"           => $this->offer_start_date ,
                "offer_end_date"             => $this->offer_end_date ,
                'category_slug'              => $this->category?->slug ,
                'price'                      =>$this->offer_start_date? Carbon::now()->between($this->offer_start_date , $this->offer_end_date) ?
                    AppLibrary::convertAmountFormat($price - ( ( $price / 100 ) * $this->discount )) : AppLibrary::convertAmountFormat($price):null ,
                'currency_price'             =>$this->offer_start_date? AppLibrary::currencyAmountFormat(Carbon::now()->between($this->offer_start_date ,
                    $this->offer_end_date) ? AppLibrary::convertAmountFormat($price - ( ( $price / 100 ) * $this->discount )) :
                    AppLibrary::convertAmountFormat($price)):NULL ,
                'old_price'                  => AppLibrary::convertAmountFormat($price) ,
                'old_currency_price'         => AppLibrary::currencyAmountFormat($price) ,
                'discount'                   =>$this->offer_start_date? Carbon::now()->between($this->offer_start_date , $this->offer_end_date) ?
                    AppLibrary::convertAmountFormat(( $price / 100 ) * $this->discount) : 0:NULL ,
                'discount_percentage'        => AppLibrary::convertAmountFormat($this->discount) ,
                'flash_sale'                 => $this->add_to_flash_sale == Ask::YES ,
                'is_offer'                   => $this->offer_start_date && $this->offer_end_date && Carbon::now()->between( $this->offer_start_date , $this->offer_end_date ) ,
                'rating_star'                => $this->rating_star ,
                'rating_star_count'          => $this->rating_star_count ,
                'stock'                      => $this->show_stock_out == Activity::DISABLE ? ( $this->can_purchasable == Ask::NO ? (int) config( 'system.non_purchase_quantity' ) : (int) $this->stock_items_sum_quantity ) : 0 ,
                'taxes'                      => SimpleTaxResource::collection($this->taxes) ,
                'thumb'                      => $this->thumb ,
                "barcode_image"              => $this->barcodeImage ,
            ];
        }
    }

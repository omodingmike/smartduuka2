<?php

    namespace App\Http\Resources;

    use App\Enums\Ask;
    use App\Libraries\AppLibrary;
    use App\Models\ProductVariation;
    use Illuminate\Http\Resources\Json\JsonResource;

    class OrderProductResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                'id'                      => $this->id ,
                'order_id'                => $this->order_id ,
                'product_id'              => $this->item_id ,
                'product_name'            => $this->item?->name ,
                'product_image'           => $this->item?->thumb ,
                'product_slug'            => $this->item?->slug ,
                'category_name'           => $this?->item?->category?->name ,
                'price'                   => $this->unit_price ,
                'currency_price'          => AppLibrary::currencyAmountFormat($this->unit_price) ,
                'quantity'                => abs($this->quantity) ,
                'discount'                => 0 ,
                'discount_currency_price' => AppLibrary::currencyAmountFormat(0) ,
                'tax'                     => 0 ,
                'sku'                     => $this->item?->sku ,
                'tax_currency'            => AppLibrary::currencyAmountFormat(0) ,
                'subtotal'                => AppLibrary::flatAmountFormat($this->total) ,
                'total'                   => AppLibrary::flatAmountFormat($this->total) ,
                'subtotal_currency_price' => AppLibrary::currencyAmountFormat($this->total) ,
                'total_currency_price'    => AppLibrary::currencyAmountFormat($this->total) ,
                'unit'                    => new UnitResource($this->item?->unit) ,
            ];
        }
    }

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
                'order_id'                => $this->model_id ,
                'product_id'              => $this->product_id ,
                'product_name'            => $this->product?->name ,
                'product_image'           => $this->product?->thumb ,
                'product_slug'            => $this->product?->slug ,
                'category_name'           => $this?->product?->category?->name ,
                'price'                   => $this->price ,
                'currency_price'          => AppLibrary::currencyAmountFormat($this->price) ,
                'quantity'                => abs($this->quantity) ,
                'other_quantity'          => abs($this->other_quantity) ,
                'purchase_quantity'       => $this->purchase_quantity ,
                'order_quantity'          => (int) ( abs($this->quantity) * abs($this->rate) ) ,
                'discount'                => $this->discount ,
                'discount_currency_price' => AppLibrary::currencyAmountFormat($this->discount) ,
                'tax'                     => $this->tax ,
                'sku'                     => $this->sku ,
                'tax_currency'            => AppLibrary::currencyAmountFormat($this->tax) ,
                'subtotal'                => AppLibrary::flatAmountFormat($this->subtotal) ,
                'total'                   => AppLibrary::flatAmountFormat($this->total) ,
                'subtotal_currency_price' => AppLibrary::currencyAmountFormat($this->subtotal) ,
                'total_currency_price'    => AppLibrary::currencyAmountFormat($this->total) ,
                'status'                  => $this->status ,
//                'unit'                    => $this->product->unit ,
                'unit'                    => new UnitResource($this->unit) ,
                'rate'                    => $this->rate ,
//                'other_unit'              => $this->product->otherUnit ,
//                'variation_names'         => $this->variation_names ,
//                'product_user_review'     => $this?->product?->userReview ? true : false ,
//                'product_user_review_id'  => $this?->product?->userReview?->id ,
//                'is_refundable'           => $this?->product?->refundable === Ask::YES ? true : false ,
//                'has_variation'           => $this->item_type == ProductVariation::class ? true : false ,
//                'variation_id'            => $this->item_type == ProductVariation::class ? $this->product_id : ''
            ];
        }
    }

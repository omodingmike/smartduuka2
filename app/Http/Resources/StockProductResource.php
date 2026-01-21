<?php

    namespace App\Http\Resources;

    use App\Models\StockProduct;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin StockProduct */
    class StockProductResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'             => $this->id ,
                'item_type'      => $this->item_type ,
                'item_id'        => $this->item_id ,
                'stock_id'       => $this->stock_id ,
                'quantity'       => $this->quantity ,
                'subtotal'       => $this->subtotal ,
                'total'          => $this->total ,
                'expiry_date'    => $this->expiry_date ,
                'weight'         => $this->weight ,
                'serial'         => $this->serial ,
                'product'        => new  ProductAdminResource( $this->whenLoaded( 'item' ) ) ,
                'notes'          => $this->notes ,
                'difference'     => $this->difference ,
                'discrepancy'    => $this->discrepancy ,
                'classification' => $this->classification ,
            ];
        }
    }

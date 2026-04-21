<?php

    namespace App\Http\Resources;

    use App\Models\ServiceItem;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin ServiceItem */
    class ServiceItemResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'             => $this->id ,
                'item_id'        => $this->item_id ,
                'item_type'      => $this->item_type ,
                'quantity'       => $this->quantity ,
                'price'          => $this->price ,
                'price_currency' => currency( $this->price ) ,
                'price_id'       => $this->price_id ,
                'price_type'     => $this->price_type ,
                'total'          => $this->total ,
                'item_name'      => $this->item->name ,
                'total_currency' => currency( $this->total ) ,
            ];
        }
    }

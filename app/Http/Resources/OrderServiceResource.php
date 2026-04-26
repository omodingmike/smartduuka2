<?php

    namespace App\Http\Resources;

    use App\Models\OrderServiceProduct;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin OrderServiceProduct */
    class OrderServiceResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'quantity'   => $this->quantity ,
                'total'      => $this->total ,
                'unit_price' => $this->unit_price ,
                'service_id' => $this->service_id ,
                'service'    => ServiceResource::collection( $this->service ) ,
                'order_id'   => $this->order_id ,
            ];
        }
    }

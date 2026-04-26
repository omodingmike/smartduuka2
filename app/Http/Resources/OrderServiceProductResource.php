<?php

    namespace App\Http\Resources;

    use App\Models\OrderServiceProduct;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin OrderServiceProduct
     */
    class OrderServiceProductResource extends JsonResource
    {
        public function toArray($request)
        {
            return [
                'id'         => $this->id ,
                'service'    => new ServiceResource( $this->whenLoaded( 'service' ) ) ,
                'addons'     => OrderServiceAdonResource::collection( $this->whenLoaded( 'addons' ) ) ,
                'tier'       => new ServiceTierResource( $this->whenLoaded( 'tier' ) ) ,
                'quantity'   => $this->quantity ,
                'unit_price' => $this->unit_price ,
                'total'      => $this->total ,
            ];
        }
    }

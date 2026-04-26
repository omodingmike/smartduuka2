<?php

    namespace App\Http\Resources;

    use App\Models\OrderServiceAdon;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin  OrderServiceAdon
     */
    class OrderServiceAdonResource extends JsonResource
    {

        public function toArray(Request $request) : array
        {
            return [
                'id'                       => $this->id ,
                'addon_id'                 => $this->addon_id ,
                'addon'                    => new ServiceAddOnResource ( $this->addon ) ,
                'order_service_product_id' => $this->order_service_product_id ,
            ];
        }
    }

<?php

    namespace App\Http\Resources;

    use App\Models\ServiceTier;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin ServiceTier */
    class ServiceTierResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'             => $this->id ,
                'name'           => $this->name ,
                'price'          => $this->price ,
                'price_currency' => currency( $this->price ) ,
                'features'       => $this->features ,
                'service_id'     => $this->service_id ,
            ];
        }
    }

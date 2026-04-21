<?php

    namespace App\Http\Resources;

    use App\Models\ServiceAddOn;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin ServiceAddOn */
    class ServiceAddOnResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'             => $this->id ,
                'name'           => $this->name ,
                'price'          => $this->price ,
                'price_currency' => currency( $this->price ) ,
                'service_id'     => $this->service_id ,
            ];
        }
    }

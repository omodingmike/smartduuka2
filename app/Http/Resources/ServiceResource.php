<?php

    namespace App\Http\Resources;

    use App\Models\Service;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Service */
    class ServiceResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                  => $this->id ,
                'name'                => $this->name ,
                'base_price'          => $this->base_price ,
                'base_price_currency' => currency( $this->base_price ) ,
                'duration'            => $this->duration ,
                'description'         => $this->description ,
                'created_at'          => $this->created_at ,
                'type'                => $this->type ,
                'status'              => $this->status ,
                'service_type'        => $this->service_type ,
                'service_category_id' => $this->service_category_id ,
                'serviceCategory'     => new ServiceCategoryResource( $this->whenLoaded( 'serviceCategory' ) ) ,
                'addOns'              => ServiceAddOnResource::collection( $this->whenLoaded( 'addOns' ) ) ,
                'tiers'               => ServiceTierResource::collection( $this->whenLoaded( 'tiers' ) ) ,
                'items'               => ServiceItemResource::collection( $this->whenLoaded( 'items' ) ) ,
            ];
        }
    }

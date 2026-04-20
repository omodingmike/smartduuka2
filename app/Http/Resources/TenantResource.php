<?php

    namespace App\Http\Resources;

    use App\Models\Tenant;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin Tenant
     */
    class TenantResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'            => $this->id ,
                'business_id'   => $this->business_id ,
                'frontend_url'  => $this->frontend_url ,
                'name'          => $this?->name ,
                'type'          => $this?->type ,
                'location'      => $this?->location ,
                'email'         => $this?->email ,
                'phone'         => $this?->phone ,
                'created_at'    => datetime( $this->created_at ) ,
                'status'        => $this->status ,
                'domains_count' => $this->domains_count ,
                'domains'       => DomainResource::collection( $this->domains ) ,
            ];
        }
    }

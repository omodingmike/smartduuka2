<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class AddressResource extends JsonResource
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
                "id"           => $this->id ,
                "user_id"      => $this->user_id ,
                "address_type" => $this->address_type ,
                "full_name"    => $this->full_name ,
                "email"        => $this->email === null ? '' : $this->email ,
                "country_code" => $this->country_code ,
                "phone"        => $this->phone ,
                "address"      => $this->address ,
                "country"      => $this->country ,
                "country_id"   => $this->country_id ?? null ,
                "state"        => $this->state === null ? '' : $this->state ,
                "state_id"     => $this->state_id ?? null ,
                "city"         => $this->city === null ? '' : $this->city ,
                "city_id"      => $this->city_id ?? null ,
                "zip_code"     => $this->zip_code === null ? '' : $this->zip_code ,
                "latitude"     => $this->latitude ,
                "longitude"    => $this->longitude ,
            ];
        }
    }

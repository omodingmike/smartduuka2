<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class SupplierResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {


            return [
                "id"           => $this->id ,
                "company"      => $this->company ,
                "status"       => $this->status ,
                "name"         => $this->name ,
                "tin"          => $this->tin ,
                "showTax"      => $this->tin ? 1 : 0 ,
                "products"     => [] ,
                "invoices"     => [] ,
                "balance"      => 0 ,
                "email"        => $this->email === NULL ? '' : $this->email ,
                "phone"        => $this->phone === NULL ? '' : $this->phone ,
                "country_code" => $this->country_code === NULL ? '' : $this->country_code ,
                "address"      => $this->address === NULL ? '' : $this->address ,
                "country"      => $this->country === NULL ? '' : $this->country ,
                "state"        => $this->state === NULL ? '' : $this->state ,
                "city"         => $this->city === NULL ? '' : $this->city ,
                "zip_code"     => $this->zip_code === NULL ? '' : $this->zip_code ,
                "image"        => $this->image ,
            ];
        }
    }
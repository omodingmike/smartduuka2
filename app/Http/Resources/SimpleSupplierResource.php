<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class SimpleSupplierResource extends JsonResource
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
                "id"       => $this->id ,
                "company"  => $this->company ,
                "status"   => $this->status ,
                "name"     => $this->name ,
                "tin"      => $this->tin ,
                "showTax"  => $this->tin ? 1 : 0 ,
                "products" => [] ,
                "invoices" => [] ,
                "balance"  => 0 ,
                "email"    => $this->email === NULL ? '' : $this->email ,
                "phone"    => $this->phone === NULL ? '' : $this->phone ,
                "address"  => $this->address === NULL ? '' : $this->address ,
            ];
        }
    }
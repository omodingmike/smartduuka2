<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use Illuminate\Http\Resources\Json\JsonResource;

    class CustomerResource extends JsonResource
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
                "id"            => $this->id ,
                "name"          => $this->name ,
                "username"      => $this->username ,
                "email"         => $this->email ,
                "type"          => $this->email ,
                "phone"         => $this->phone === NULL ? '' : $this->phone ,
                "status"        => $this->status ,
                "credits"       => AppLibrary::currencyAmountFormat( $this->credits ) ,
                "show_pay"      => $this->credits > 0 ,
                "show_pay_list" => count( $this->payments ) > 0 ,
                "image"         => $this->image ,
                "notes"         => $this->notes ,
                "totalSpent"    => 0 ,
                "addresses"     => [] ,
                "orders"        => [] ,
                "creditProfile" => [] ,
                "created_at"    => AppLibrary::date( $this->created_at ) ,
            ];
        }
    }

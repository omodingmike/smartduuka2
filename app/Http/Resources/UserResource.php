<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use Illuminate\Http\Resources\Json\JsonResource;

    class UserResource extends JsonResource
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
                "id"               => $this->id ,
                "name"             => $this->name ,
                "phone"            => $this->phone === NULL ? '' : $this->phone ,
                "email"            => $this->email ,
                'username'         => $this->username ,
                'commission'       => $this->commission ,
                "balance"          => AppLibrary::flatAmountFormat( $this->balance ) ,
                "currency_balance" => AppLibrary::currencyAmountFormat( $this->balance ) ,
                "image"            => $this->thumb ,
                "addresses"        => $this->addresses ,
                "role_id"          => $this->myRole ,
                "country_code"     => $this->country_code ,
                "order"            => $this->orders->count() ,
                'create_date'      => AppLibrary::date( $this->created_at ) ,
                'update_date'      => AppLibrary::date( $this->updated_at ) ,
            ];
        }
    }

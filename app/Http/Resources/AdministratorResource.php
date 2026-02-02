<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use Illuminate\Http\Resources\Json\JsonResource;

    class AdministratorResource extends JsonResource
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
                "id"                  => $this->id ,
                "name"                => $this->name ,
                "username"            => $this->username ,
                "email"               => $this->email ,
                "phone"               => $this->phone ,
                "status"              => $this->status ,
                "role_id"             => optional( $this->roles[ 0 ] )->id ,
                "role"                => optional( $this->roles[ 0 ] )->name ,
                "image"               => $this->image ,
                "country_code"        => $this->country_code ,
                "last_login_date"     => $this->last_login_date ? datetime( $this->last_login_date ) : NULL ,
                "total_revenue"       => AppLibrary::currencyAmountFormat( $this->total_revenue ) ,
                "average_order_value" => AppLibrary::currencyAmountFormat( $this->average_order_value ) ,
                "orders"              => OrderResource::collection( $this->orders ) ,
            ];
        }
    }

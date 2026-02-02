<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\User;
    use Illuminate\Http\Resources\Json\JsonResource;

    class EmployeeResource extends JsonResource
    {
        /** @mixin User */
        public function toArray($request) : array
        {
            return [
                "id"                  => $this->id ,
                "name"                => $this->name ,
                "username"            => $this->username ,
                "email"               => $this->email ,
                "sales"               => AppLibrary::currencyAmountFormat( ( $this->sales ) ) ,
                "commission"          => AppLibrary::currencyAmountFormat( ( $this->commission ) ) ,
                "phone"               => $this->phone === NULL ? '' : $this->phone ,
                "status"              => $this->status ,
                "role_id"             => optional( $this->roles[ 0 ] )->id ,
                "role"                => optional( $this->roles[ 0 ] )->name ,
                "image"               => $this->image ,
                "country_code"        => $this->country_code ,
                "department"          => $this->department ,
                "force_reset"         => $this->force_reset ,
                "last_login_date"     => $this->last_login_date ? AppLibrary::datetime2( $this->last_login_date ) : NULL ,
                "total_revenue"       => AppLibrary::currencyAmountFormat( $this->total_revenue ) ,
                "created_at"          => datetime( $this->created_at ) ,
                "average_order_value" => AppLibrary::currencyAmountFormat( $this->average_order_value ) ,
                "permissions"         => $this->getPermissionNames() ,
            ];
        }
    }

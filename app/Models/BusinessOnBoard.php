<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;

    class BusinessOnBoard extends Model
    {
        protected $fillable = [
            'name' ,
            'tenant' ,
            'email' ,
            'phone' ,
            'mobile_phone_number' ,
            'address' ,
            'admin_email' ,
            'admin_password' ,
            'admin_pin' ,
            'payment_method' ,
            'plan_id' ,
            'cycle_id' ,
            'amount' ,
            'admin_name' ,
        ];

        protected function domain() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->tenant . config( 'session.domain' ) ,
            );
        }
    }

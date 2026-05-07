<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class SubscriptionPlan extends Model
    {
        protected $fillable = [
            'name' ,
            'description' ,
            'features' ,
            'base_amount' ,
            'popular' ,
        ];
        protected $casts    = [ 'popular' => 'boolean' , 'features' => 'array' ];
    }

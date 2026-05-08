<?php

    namespace App\Models;

    use App\Enums\SubscriptionPlanType;
    use Illuminate\Database\Eloquent\Model;

    class SubscriptionPlan extends Model
    {
        protected $fillable = [
            'name' ,
            'description' ,
            'features' ,
            'base_amount' ,
            'popular' , 'type' , 'setup'
        ];
        protected $casts    = [ 'popular' => 'boolean' , 'features' => 'array' , 'type' => SubscriptionPlanType::class , 'setup' => 'integer' ];
    }

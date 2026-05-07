<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class BillingCycle extends Model
    {
        protected $fillable = [
            'name' ,
            'multiplier' ,
            'discount' ,
        ];

        protected function casts() : array
        {
            return [
                'discount' => 'float'
            ];
        }
    }

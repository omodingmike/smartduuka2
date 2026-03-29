<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class CustomerLedger extends Model
    {
        protected $fillable = [
            'date' ,
            'reference' ,
            'description' ,
            'bill_amount' ,
            'paid' ,
            'balance' ,
            'user_id' ,
        ];

        protected function casts() : array
        {
            return [
                'date' => 'datetime' ,
            ];
        }
    }

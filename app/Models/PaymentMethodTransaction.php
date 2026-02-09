<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class PaymentMethodTransaction extends Model
    {
        protected $fillable = [
            'amount' ,
            'charge' ,
            'description' ,
            'payment_method_id' ,
            'order_id' ,
        ];

        public function paymentMethod() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class );
        }
    }

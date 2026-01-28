<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class OrderPaymentMethod extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'payment_method_id' ,
            'amount' ,
            'order_id' ,
        ];

        public function paymentMethod() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class , 'payment_method_id' , 'id' );
        }
    }

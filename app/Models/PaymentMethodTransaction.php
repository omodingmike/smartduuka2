<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class PaymentMethodTransaction extends Model
    {
        protected $fillable = [
           'amount', 'charge', 'description', 'payment_method_id', 'item_id', 'item_type'
        ];

        public function paymentMethod() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class );
        }
    }

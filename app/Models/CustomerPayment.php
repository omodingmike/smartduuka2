<?php

    namespace App\Models;

    use App\Enums\CustomerPaymentType;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class CustomerPayment extends Model
    {
        use HasFactory;

        protected $guarded = [];

        protected function casts() : array
        {
            return [
                'customer_payment_type' => CustomerPaymentType::class
            ];
        }

        public function paymentMethod() : HasOne
        {
            return $this->hasOne( PaymentMethod::class , 'id' , 'payment_method_id' );
        }

        public function customer() : BelongsTo
        {
            return $this->belongsTo( User::class , 'user_id' );
        }
    }

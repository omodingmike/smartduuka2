<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class CustomerPayment extends Model
    {
        use HasFactory;

        protected $guarded = [];

        public function paymentMethod() : HasOne
        {
            return $this->hasOne(PaymentMethod::class , 'id' , 'payment_method_id');
        }
    }

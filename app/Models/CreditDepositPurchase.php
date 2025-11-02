<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class CreditDepositPurchase extends Model
    {
        use HasFactory;

        protected $fillable = [
            'order_id' ,
            'user_id' ,
            'type' ,
            'paid' ,
            'balance','date'
        ];

        public function paymentMethod() : HasOne
        {
            return $this->hasOne(PaymentMethod::class , 'id' , 'payment_method_id');
        }
    }

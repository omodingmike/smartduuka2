<?php

    namespace App\Models;

    use App\Enums\CustomerWalletTransactionType;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class CustomerWalletTransaction extends Model
    {

        protected $fillable = [
            'user_id' ,
            'amount' ,
            'payment_method_id' ,
            'reference' ,
            'type' ,
            'balance' ,
        ];

        protected $casts = [ 'type' => CustomerWalletTransactionType::class ];

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        public function paymentMethod() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class );
        }
    }

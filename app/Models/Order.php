<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Illuminate\Database\Eloquent\Relations\morphMany;

    class Order extends Model
    {
        use HasFactory;

        protected $table    = "orders";
        protected $fillable = [
            'order_serial_no' ,
            'user_id' ,
            'tax' ,
            'discount' ,
            'subtotal' ,
            'total' ,
            'shipping_charge' ,
            'order_type' ,
            'order_datetime' ,
            'payment_method' ,
            'payment_status' ,
            'status' ,
            'paid' ,
            'reason' ,
            'source' ,
            'pos_payment_method' ,
            'pos_payment_note' , 'original_type' , 'due_date' , 'balance' ,
            'change'
        ];

        protected $casts = [
            'id'                 => 'integer' ,
            'order_serial_no'    => 'string' ,
            'user_id'            => 'integer' ,
            'original_type'      => 'integer' ,
            'tax'                => 'decimal:6' ,
            'discount'           => 'decimal:2' ,
            'subtotal'           => 'decimal:6' ,
            'total'              => 'decimal:6' ,
            'shipping_charge'    => 'decimal:6' ,
            'order_type'         => 'integer' ,
            'order_datetime'     => 'datetime' ,
            'payment_method'     => 'integer' ,
            'payment_status'     => 'integer' ,
            'status'             => 'integer' ,
            'reason'             => 'string' ,
            'source'             => 'integer' ,
            'pos_payment_method' => 'integer' ,
            'pos_payment_note'   => 'string'
        ];

        public function orderProducts() : morphMany
        {
            return $this->morphMany(Stock::class , 'model');
        }

        public function stocks() : MorphMany
        {
            return $this->morphMany(Stock::class , 'model');
        }

        public function paymentMethod() : HasOne
        {
            return $this->hasOne(PaymentMethod::class , 'id' , 'payment_method');
        }

        public function user() : BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        public function creditDepositPurchases() : Order | Builder | HasMany
        {
            return $this->hasMany(CreditDepositPurchase::class , 'order_id')->whereNotNull('payment_method_id')->latest();
        }
    }

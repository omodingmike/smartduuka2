<?php

    namespace App\Models;

    use App\Enums\OrderChannel;
    use App\Enums\OrderStatus;
    use App\Enums\OrderType;
    use App\Enums\PaymentStatus;
    use App\Enums\PaymentType;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\morphMany;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class Order extends Model
    {
        use HasFactory;

        protected $table    = "orders";
        protected $appends  = [ 'net_paid' ];
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
            'change' ,
            'creator_id' ,
            'creator_type' ,
            'payment_type' ,
            'channel' ,
            'register_id' ,
            'warehouse_id'
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
            'order_type'         => OrderType::class ,
            'order_datetime'     => 'datetime' ,
            'payment_method'     => 'integer' ,
            'payment_status'     => PaymentStatus::class ,
            'status'             => OrderStatus::class ,
            'payment_type'       => PaymentType::class ,
            'channel'            => OrderChannel::class ,
            'reason'             => 'string' ,
            'source'             => 'integer' ,
            'pos_payment_method' => 'integer' ,
            'pos_payment_note'   => 'string'
        ];

        public function orderProducts() : HasMany
        {
            return $this->hasMany( OrderProduct::class );
        }

        
        public function stocks() : MorphMany
        {
            return $this->morphMany( Stock::class , 'model' );
        }

        public function getNetPaidAttribute()
        {
            return $this->posPayments()->sum( 'amount' );
        }

        public function getBalanceAttribute()
        {
            return $this->total - $this->posPayments()->sum( 'amount' );
        }

        public function paymentMethods()
        {
            return $this->hasMany( PosPayment::class , 'order_id' , 'id' );
        }

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        public function creditDepositPurchases() : Order | Builder | HasMany
        {
            return $this->hasMany( CreditDepositPurchase::class , 'order_id' )->whereNotNull( 'payment_method_id' )->latest();
        }

        public function posPayments() : HasMany
        {
            return $this->hasMany( PosPayment::class );
        }

        public function creator() : MorphTo
        {
            return $this->morphTo();
        }

        public function paymentMethodTransactions() : MorphMany
        {
            return $this->morphMany( PaymentMethodTransaction::class , 'item' );
        }
    }

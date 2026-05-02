<?php

    namespace App\Models;

    use App\Enums\OrderChannel;
    use App\Enums\OrderStatus;
    use App\Enums\OrderType;
    use App\Enums\Pad;
    use App\Enums\PaymentStatus;
    use App\Enums\PaymentType;
    use App\Enums\PreOrderStatus;
    use App\Enums\QuotationStatus;
    use App\Enums\QuotationType;
    use App\Enums\RefundStatus;
    use App\Enums\ReturnStatus;
    use App\Enums\ReturnType;
    use Illuminate\Database\Eloquent\Attributes\Scope;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\morphMany;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Illuminate\Support\Str;

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
            'warehouse_id' ,
            'pre_order_status' , 'active' , 'user_type' , 'editor_type' , 'editor_id' , 'delivery_address' , 'delivery_fee' , 'refund_status' , 'return_status' , 'return_type' , 'original_order_id' ,
            'is_returned' ,
            'quotation_status' ,
            'offer_amount' ,
            'offer_message' , 'quotation_type' ,
            'decline_message'
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
            'quotation_type'     => QuotationType::class ,
            'order_datetime'     => 'datetime' ,
            'due_date'           => 'datetime' ,
            'payment_method'     => 'integer' ,
            'payment_status'     => PaymentStatus::class ,
            'status'             => OrderStatus::class ,
            'quotation_status'   => QuotationStatus::class ,
            'payment_type'       => PaymentType::class ,
            'channel'            => OrderChannel::class ,
            'pre_order_status'   => PreOrderStatus::class ,
            'refund_status'      => RefundStatus::class ,
            'return_status'      => ReturnStatus::class ,
            'return_type'        => ReturnType::class ,
            'reason'             => 'string' ,
            'source'             => 'integer' ,
            'pos_payment_method' => 'integer' ,
            'pos_payment_note'   => 'string'
        ];

        public function orderProducts() : HasMany
        {
            return $this->hasMany( OrderProduct::class );
        }

        public function orderServiceProducts() : HasMany
        {
            return $this->hasMany( OrderServiceProduct::class );
        }

        public function taxes() : MorphMany
        {
            return $this->morphMany( ItemTax::class , 'item' );
        }

        public function getBalanceAttribute()
        {
            return $this->total - $this->posPayments()->sum( 'amount' );
        }

        protected function orderSerialNo() : Attribute
        {
            return Attribute::make(
                get: function () {
                    $id           = $this->id;
                    $payment_type = $this->payment_type;
                    $prefix       = match ( $payment_type ) {
                        PaymentType::PREORDER  => 'PRE-' ,
                        PaymentType::RETURN    => 'RTN-' ,
                        PaymentType::QUOTATION => 'QT-' ,
                        default                => 'ORD-'
                    };
                    return $prefix . Str::padLeft( $id , Pad::LENGTH , '0' );
                } ,
            );
        }

        #[Scope]
        protected function active(Builder $query) : void
        {
            $query->where( function (Builder $q) {
                $q->whereNull( 'return_status' )
                  ->orWhereNotIn( 'return_status' , [
                      ReturnStatus::CANCELED->value ,
                      ReturnStatus::REJECTED->value
                  ] );
            } )
                  ->where( function (Builder $q) {
                      $q->whereNotIn( 'pre_order_status' , [ PreOrderStatus::REFUNDED , PreOrderStatus::CANCELED ] )
                        ->orWhereNull( 'pre_order_status' );
                  } )
                  ->where( function (Builder $q) {
                      $q->where( 'status' , '!=' , OrderStatus::CANCELED )
//                        ->orWhere( 'quotation_status' , QuotationStatus::CONVERTED )
                        ->orWhereNull( 'status' );
                  } );
        }

//    ->where( 'quotation_status' , QuotationStatus::CONVERTED )

        public function stocks() : MorphMany
        {
            return $this->morphMany( Stock::class , 'model' );
        }

        public function getNetPaidAttribute()
        {
            return $this->posPayments()->sum( 'amount' );
        }


        public function paymentMethods()
        {
            return $this->hasMany( PosPayment::class , 'order_id' , 'id' );
        }

        public function originalOrder()
        {
            return $this->belongsTo( Order::class , 'original_order_id' , 'id' );
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

        public function totalCost()
        {
            return $this->orderProducts->sum( function (OrderProduct $orderProduct) {
                return $orderProduct->totalCost();
            } );
        }
    }

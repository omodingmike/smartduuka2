<?php

    namespace App\Models;

    use App\Enums\CleaningOrderStatus;
    use App\Enums\DeliveryType;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class CleaningOrder extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'order_id' ,
            'cleaning_service_customer_id' ,
            'total' ,
            'date' ,
            'status' ,
            'service_method' ,
            'cleaning_service_id' ,
            'subtotal' ,
            'tax' ,
            'discount' ,
            'payment_method_id' ,
            'paid' ,
            'balance' ,'address'
        ];

        public function cleaningServiceCustomer() : BelongsTo
        {
            return $this->belongsTo( CleaningServiceCustomer::class );
        }

        public function items() : BelongsToMany
        {
            return $this->belongsToMany( CleaningOrderItem::class , 'cleaning_order_item' , 'cleaning_order_id' , 'cleaning_order_item_id' );
        }

        public function paymentMethod() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class );
        }

        protected function casts() : array
        {
            return [
                'date'           => 'datetime' ,
                'status'         => CleaningOrderStatus::class ,
                'service_method' => DeliveryType::class
            ];
        }
    }

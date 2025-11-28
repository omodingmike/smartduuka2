<?php

    namespace App\Models;

    use App\Enums\CleaningOrderStatus;
    use App\Enums\DeliveryType;
    use App\Enums\MediaEnum;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Spatie\MediaLibrary\HasMedia;

    class CleaningOrder extends Model implements HasMedia
    {
        use SoftDeletes , HasImageMedia;


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
            'balance' , 'address'
        ];

        protected function getMediaCollection() : string
        {
            return MediaEnum::ORDERS_COLLECTION;
        }

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

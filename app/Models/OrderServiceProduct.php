<?php

    namespace App\Models;

    use App\Enums\ItemType;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class OrderServiceProduct extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'order_id' ,
            'quantity' ,
            'total' ,
            'unit_price' ,
            'service_id' ,
            'quotation_item_type' ,
        ];
        protected $table    = 'order_services';
        protected $casts    = [ 'quotation_item_type' => ItemType::class ];

        public function order() : BelongsTo
        {
            return $this->belongsTo( Order::class );
        }

        public function service() : BelongsTo
        {
            return $this->belongsTo( Service::class );
        }

        public function addons() : HasMany
        {
            return $this->hasMany( OrderServiceAdon::class );
        }

        public function tier() : HasOne
        {
            return $this->hasOne( OrderServiceTier::class );
        }
    }

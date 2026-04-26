<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class OrderServiceTier extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'order_service_product_id' ,
            'service_tier_id' ,
        ];

        public function orderServiceProduct() : BelongsTo
        {
            return $this->belongsTo( OrderServiceProduct::class );
        }

        public function serviceTier() : BelongsTo
        {
            return $this->belongsTo( ServiceTier::class );
        }
    }

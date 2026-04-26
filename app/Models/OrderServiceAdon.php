<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class OrderServiceAdon extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'order_service_product_id' ,
            'addon_id' ,
        ];

        public function orderServiceProduct() : BelongsTo
        {
            return $this->belongsTo( OrderServiceProduct::class );
        }

        public function addon() : BelongsTo
        {
            return $this->belongsTo( ServiceAddOn::class , 'addon_id' );
        }
    }

<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class ServiceItem extends Model
    {
        protected $fillable = [
            'item_id' ,
            'item_type' ,
            'quantity' ,
            'price_id' ,
            'price_type' ,
            'total' ,
            'service_id' ,
            'price' ,
        ];

        public function item() : MorphTo
        {
            return $this->morphTo();
        }
    }

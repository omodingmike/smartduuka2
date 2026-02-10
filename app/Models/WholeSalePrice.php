<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class WholeSalePrice extends Model
    {
        public    $timestamps = FALSE;
        protected $table      = 'whole_sale_prices';

        protected $fillable = [
            'minQuantity' ,
            'price' ,
            'item_id' ,
            'item_type' ,
        ];

        public function item() : MorphTo
        {
            return $this->morphTo();
        }
    }

<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class WholeSalePrice extends Model
    {
        public $timestamps = FALSE;
        protected $table ='whole_sale_prices';

        protected $fillable = [
            'minQuantity' ,
            'price' ,
            'product_id' ,
        ];

        public function product() : BelongsTo
        {
            return $this->belongsTo( Product::class );
        }
    }

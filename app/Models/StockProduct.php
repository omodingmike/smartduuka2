<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class StockProduct extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'item_type' ,
            'item_id' ,
            'stock_id' ,
            'quantity' ,
            'subtotal' ,
            'total' ,
            'expiry_date' ,
            'difference' ,
            'discrepancy' ,
            'classification' ,
            'weight' ,
            'serial' ,
            'expiry' ,
            'unit_id' ,
        ];

        public function item() : MorphTo
        {
            return $this->morphTo();
        }

        public function stock() : BelongsTo
        {
            return $this->belongsTo( Stock::class );
        }

        protected function casts() : array
        {
            return [
                'expiry_date' => 'datetime' ,
            ];
        }
    }

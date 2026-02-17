<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class RetailPriceUpdate extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'unit_id' ,
            'old_price' ,
            'new_price' , 'item_id' , 'item_type','purchase_id'
        ];

        public function item() : MorphTo
        {
            return $this->morphTo();
        }

        public function unit() : BelongsTo
        {
            return $this->belongsTo( Unit::class );
        }

        public function purchase() : BelongsTo
        {
            return $this->belongsTo( Purchase::class );
        }
    }

<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class ItemTax extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'item_id' ,
            'item_type' ,
            'tax_id' ,
        ];

        public function item() : MorphTo
        {
            return $this->morphTo();
        }

        public function tax() : BelongsTo
        {
            return $this->belongsTo( Tax::class );
        }
    }

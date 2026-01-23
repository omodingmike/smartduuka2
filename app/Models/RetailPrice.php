<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class RetailPrice extends Model
    {
        use HasFactory;

        public    $timestamps = false;
        protected $guarded    = [];
        protected $casts      = [
            'price' => 'integer'
        ];
        public function item(): MorphTo
        {
            return $this->morphTo();
        }
        public function unit() : BelongsTo
        {
            return $this->belongsTo(Unit::class , 'unit_id' , 'id');
        }
}

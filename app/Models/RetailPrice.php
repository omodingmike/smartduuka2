<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class RetailPrice extends Model
    {
        use HasFactory;

        public    $timestamps = false;
        protected $guarded    = [];
        protected $casts      = [
            'price' => 'integer'
        ];
        public function unit() : BelongsTo
        {
            return $this->belongsTo(Unit::class , 'unit_id' , 'id');
        }
}

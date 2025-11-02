<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Support\Str;

    class ProductionSetup extends Model
    {
        use HasFactory;

        protected $guarded = [];

//        public function id() : Attribute
//        {
//            return Attribute::make(
//                get: fn(mixed $value) => Str::padLeft($value , 5 , 0) ,
//            );
//        }

        public function product() : BelongsTo
        {
            return $this->belongsTo(Product::class);
        }
    }

<?php

    namespace App\Models;

    use App\Enums\DamageStatus;
    use App\Enums\MediaEnum;
    use App\Enums\Pad;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\morphMany;
    use Illuminate\Support\Str;
    use Spatie\MediaLibrary\HasMedia;

    class Damage extends Model implements HasMedia
    {
        use HasFactory;
        use HasImageMedia;

        protected $fillable = [
            'date' ,
            'reference_no' ,
            'subtotal' ,
            'tax' ,
            'discount' ,
            'total' ,
            'note' ,
            'reason' ,
            'creator_id' ,
            'status'
        ];

        protected $casts = [
            'id'       => 'integer' ,
            'date'     => 'datetime' ,
            'subtotal' => 'decimal:6' ,
            'tax'      => 'decimal:6' ,
            'discount' => 'decimal:6' ,
            'total'    => 'integer' ,
            'note'     => 'string' ,
            'status'   => DamageStatus::class
        ];

        protected function getMediaCollectionName() : string
        {
            return MediaEnum::DAMAGES;
        }

        protected function referenceNo() : Attribute
        {
            return Attribute::make(
                get: function (string $value) {
                    $id = $this->id;
                    return 'D-' . Str::padLeft( $id , Pad::LENGTH , '0' );
                } ,
            );
        }

        public function stocks() : morphMany
        {
            return $this->morphMany( Stock::class , 'model' );
        }

        public function creator()
        {
            return $this->belongsTo( User::class , 'creator_id' , 'id' );
        }

        public function getFileAttribute()
        {
            if ( ! empty( $this->getFirstMediaUrl( 'damage' ) ) ) {
                $product = $this->getMedia( 'damage' )->first();
                return $product->getUrl();
            }
        }
    }

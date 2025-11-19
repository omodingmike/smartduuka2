<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Spatie\MediaLibrary\InteractsWithMedia;

    class CleaningService extends Model
    {
        use SoftDeletes , InteractsWithMedia;

        protected $fillable = [
            'name' ,
            'cleaning_service_category_id' ,
            'price' ,
            'description' ,
            'type' ,
        ];

        public function cleaningServiceCategory() : BelongsTo
        {
            return $this->belongsTo( CleaningServiceCategory::class );
        }

//        public function registerMediaCollections() : void
//        {
//            $this->addMediaCollection( 'service' )->singleFile();
//        }

//        protected function image() : Attribute
//        {
//            return Attribute::make(
//                get: function (string | null $value) {
//                    if ( $this->hasMedia( 'service') ) {
//                        return $this->getLastMediaUrl( 'service', 'thumb' );
//                    }
//                    return asset( 'default.png' );
//                }
//            );
//        }

//        public function registerMediaConversions(Media $media = NULL) : void
//        {
//            $this->addMediaConversion( 'thumb' )->focalCropAndResize( 168 , 180 )->sharpen( 10 );
//            $this->addMediaConversion( 'cover' )->focalCropAndResize( 372 , 405 )->sharpen( 10 );
//            $this->addMediaConversion( 'preview' )->focalCropAndResize( 768 , 768 )->sharpen( 10 );
//        }
    }

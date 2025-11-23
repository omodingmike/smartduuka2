<?php

    namespace App\Models;

    use App\Enums\MediaEnum;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Spatie\MediaLibrary\HasMedia;
    use Spatie\MediaLibrary\InteractsWithMedia;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;

    class CleaningService extends Model implements HasMedia
    {
        use SoftDeletes , InteractsWithMedia;

        protected $fillable = [
            'name' ,
            'cleaning_service_category_id' ,
            'price' ,
            'description' ,
            'type' , 'tax_id'
        ];

        public function cleaningServiceCategory() : BelongsTo
        {
            return $this->belongsTo( CleaningServiceCategory::class );
        }

        public function tax() : BelongsTo
        {
            return $this->belongsTo( Tax::class );
        }

        public function registerMediaCollections() : void
        {
            $this->addMediaCollection( MediaEnum::SERVICES_MEDIA_COLLECTION )->singleFile();
        }

        protected function image() : Attribute
        {
            return Attribute::make(
                get: function (string | null $value) {
                    if ( $this->hasMedia( MediaEnum::SERVICES_MEDIA_COLLECTION ) ) {
                        return $this->getLastMediaUrl( MediaEnum::SERVICES_MEDIA_COLLECTION , 'thumb' );
                    }
                    return asset( 'default.png' );
                }
            );
        }

        public function registerMediaConversions(Media $media = NULL) : void
        {
            $this->addMediaConversion( 'thumb' )->focalCropAndResize( 168 , 180 )->sharpen( 10 );
            $this->addMediaConversion( 'cover' )->focalCropAndResize( 372 , 405 )->sharpen( 10 );
            $this->addMediaConversion( 'preview' )->focalCropAndResize( 768 , 768 )->sharpen( 10 );
        }
    }

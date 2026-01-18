<?php

    namespace App\Traits;

    use App\Enums\MediaEnum;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Support\Str;
    use Spatie\MediaLibrary\InteractsWithMedia;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;

    trait HasImageMedia
    {
        use InteractsWithMedia;

        protected function getMediaCollection() : string
        {
            return MediaEnum::IMAGES_COLLECTION;
        }

        public function registerMediaCollections() : void
        {
            $this->addMediaCollection( $this->getMediaCollection() )->singleFile();
        }
        public function getImageAttribute() : string
        {
            if ( ! empty( $this->getLastMediaUrl( $this->getMediaCollection() ) ) ) {
                return asset( $this->getLastMediaUrl( $this->getMediaCollection() ) );
            }
            return asset( 'images/default/product/thumb.png' );
        }

    }

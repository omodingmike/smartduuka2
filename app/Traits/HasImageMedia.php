<?php

    namespace App\Traits;

    use App\Enums\MediaEnum;
    use Spatie\MediaLibrary\InteractsWithMedia;

    trait HasImageMedia
    {
        use InteractsWithMedia;

        public function getMediaCollectionName() : string
        {
            return MediaEnum::IMAGES_COLLECTION;
        }

        public function registerMediaCollections() : void
        {
            // Updated call
            $this->addMediaCollection( $this->getMediaCollectionName() )->singleFile();
        }

        public function getImageAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( $this->getMediaCollectionName() ) ) ) {
                return asset( $this->getFirstMediaUrl( $this->getMediaCollectionName() ) );
            }
            return asset( 'images/default/product/thumb.png' );
        }
    }
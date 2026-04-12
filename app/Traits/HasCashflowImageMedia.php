<?php

    namespace App\Traits;

    use App\Enums\MediaEnum;
    use Spatie\MediaLibrary\InteractsWithMedia;

    trait HasCashflowImageMedia
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

        public function getImageAttribute() : string | null
        {
            if ( ! empty( $this->getLastMediaUrl( $this->getMediaCollectionName() ) ) ) {
                return asset( $this->getLastMediaUrl( $this->getMediaCollectionName() ) );
            }
            return NULL;
        }
    }
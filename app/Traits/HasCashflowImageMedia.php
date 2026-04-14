<?php

    namespace App\Traits;

    use App\Enums\MediaEnum;
    use Illuminate\Database\Eloquent\Casts\Attribute;
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
            $this->addMediaCollection( $this->getMediaCollectionName() )->singleFile();
        }

//        public function getImageAttribute() : string | null
//        {
//            $url = $this->getLastMediaUrl( $this->getMediaCollectionName() );
//            info($url);
//            return $url ?? NULL;
//        }

        protected function image() : Attribute
        {
            return Attribute::make(
                get: fn() =>  $this->getLastMediaUrl( $this->getMediaCollectionName() )  ?: NULL ,
            );
        }
    }
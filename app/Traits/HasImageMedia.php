<?php

    namespace App\Traits;

    use App\Enums\MediaEnum;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Spatie\MediaLibrary\InteractsWithMedia;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;

    trait HasImageMedia
    {
        use InteractsWithMedia;

        /**
         * Return the media collection name.
         * Override this in the model if needed.
         */
        protected function getMediaCollection(): string
        {
            return MediaEnum::IMAGES_COLLECTION;
        }

        /**
         * Register media collection as single file.
         */
        public function registerMediaCollections(): void
        {
            $this->addMediaCollection($this->getMediaCollection())->singleFile();
        }

        /**
         * Accessor for image URL.
         * Returns 'thumb' conversion or default image.
         */
        protected function image(): Attribute
        {
            return Attribute::make(
                get: function () {
                    $collection = $this->getMediaCollection();

                    if ($this->hasMedia($collection)) {
                        return $this->getLastMediaUrl($collection, 'thumb');
                    }
                    return asset('no_image.png');
                }
            );
        }

        /**
         * Register standard media conversions.
         */
        public function registerMediaConversions(Media $media = null): void
        {
            $this->addMediaConversion('thumb')
                 ->focalCropAndResize(400, 400)
                 ->sharpen(10);

            $this->addMediaConversion('cover')
                 ->focalCropAndResize(372, 405)
                 ->sharpen(10);

            $this->addMediaConversion('preview')
                 ->focalCropAndResize(768, 768)
                 ->sharpen(10);
        }
    }

<?php

    namespace App\Models;

    use App\Enums\CacheEnum;
    use App\Enums\MediaEnum;
    use App\Traits\ForgetsCacheOnCRUD;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Spatie\MediaLibrary\HasMedia;

    class CleaningService extends Model implements HasMedia
    {
        use SoftDeletes , HasImageMedia , ForgetsCacheOnCRUD;

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

        protected function getCacheKeysToForget() : string | array
        {
            return CacheEnum::CLEANING_SERVICE_CATEGORIES;
        }

        public function tax() : BelongsTo
        {
            return $this->belongsTo( Tax::class );
        }

        public function getMediaCollection() : string
        {
            return MediaEnum::SERVICES_MEDIA_COLLECTION;
        }
    }

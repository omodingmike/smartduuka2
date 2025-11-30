<?php

    namespace App\Models;

    use App\Enums\CacheEnum;
    use App\Traits\ForgetsCacheOnCRUD;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Support\Facades\Cache;

    class CleaningServiceCategory extends Model
    {
        use SoftDeletes,  ForgetsCacheOnCRUD;

        protected $fillable = [
            'name' ,
            'description' ,
        ];

        public function services() : HasMany | CleaningServiceCategory
        {
            return $this->hasMany( CleaningService::class );
        }
        protected function getCacheKeysToForget(): string|array
        {
            return CacheEnum::CLEANING_SERVICE_CATEGORIES;
        }

        protected function name() : Attribute
        {
            return Attribute::make(
                get: fn(string $value) => ucwords( $value ) ,
            );
        }
    }

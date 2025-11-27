<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class CleaningServiceCategory extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'name' ,
            'description' ,
        ];

        public function services() : HasMany | CleaningServiceCategory
        {
            return $this->hasMany( CleaningService::class );
        }
        protected function name(): Attribute
        {
            return Attribute::make(
                get: fn (string $value) => ucwords($value),
            );
        }
    }

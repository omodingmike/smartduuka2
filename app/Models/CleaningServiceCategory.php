<?php

    namespace App\Models;

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
    }

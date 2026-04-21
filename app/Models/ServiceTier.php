<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class ServiceTier extends Model
    {
        protected $fillable = [
            'name' ,
            'price' ,
            'features' ,
            'service_id' ,
        ];

        protected $casts = [];

        public function service() : BelongsTo
        {
            return $this->belongsTo( Service::class );
        }
    }

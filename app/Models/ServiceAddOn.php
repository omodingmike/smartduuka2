<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class ServiceAddOn extends Model
    {
        protected $fillable = [
            'name' ,
            'price' ,
            'service_id' ,
        ];

        protected $casts = ['price' => 'float'];

        public function service() : BelongsTo
        {
            return $this->belongsTo( Service::class );
        }
    }

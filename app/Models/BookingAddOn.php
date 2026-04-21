<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class BookingAddOn extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'booking_id' ,
            'service_add_on_id' ,
        ];

        public function booking() : BelongsTo
        {
            return $this->belongsTo( Booking::class );
        }

        public function serviceAddOn() : BelongsTo
        {
            return $this->belongsTo( ServiceAddOn::class );
        }
    }

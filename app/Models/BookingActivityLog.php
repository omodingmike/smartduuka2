<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class BookingActivityLog extends Model
    {
        protected $fillable = [
            'status' ,
            'note' ,
            'user_id' ,
            'booking_id' ,
            'created_at' ,
        ];

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        public function booking() : BelongsTo
        {
            return $this->belongsTo( Booking::class );
        }
    }

<?php

    namespace App\Models;

    use App\Enums\BookingStatus;
    use App\Enums\Pad;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Support\Str;

    class Booking extends Model
    {
        protected $fillable = [
            'customer_name' ,
            'customer_phone' ,
            'service_id' ,
            'date' ,
            'status' ,
            'total' ,
            'notes' ,
        ];

        public function service() : BelongsTo
        {
            return $this->belongsTo( Service::class );
        }

        public function addsOn() : HasMany
        {
            return $this->hasMany( BookingAddOn::class , 'booking_id' , 'id' );
        }

        public function activityLogs() : HasMany
        {
            return $this->hasMany( BookingActivityLog::class );
        }

        protected function bookingId() : Attribute
        {
            return Attribute::make(
                get: function () {
                    $id = $this->id;
                    return 'BKG-' . Str::padLeft( $id , Pad::LENGTH , '0' );
                } ,
            );
        }

        protected function casts() : array
        {
            return [
                'date'   => 'datetime' ,
                'status' => BookingStatus::class ,
            ];
        }
    }

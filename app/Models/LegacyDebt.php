<?php

    namespace App\Models;

    use App\Enums\PaymentStatus;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class LegacyDebt extends Model
    {
        protected $fillable = [
            'user_id' ,
            'amount' ,
            'date' ,
            'notes' , 'payment_status'
        ];


        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        protected function casts() : array
        {
            return [
                'date'           => 'datetime' ,
                'payment_status' => PaymentStatus::class ,
            ];
        }
    }

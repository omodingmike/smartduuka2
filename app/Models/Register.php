<?php

    namespace App\Models;

    use App\Enums\RegisterStatus;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Register extends Model
    {
        protected $fillable = [
            'user_id' ,
            'opening_float' ,
            'status' ,
            'opened_at' ,
            'closed_at' ,
            'expected_float' ,
            'closing_float' ,
            'difference' ,
            'notes' ,
        ];

        public function posPayments()
        {
            return $this->hasMany( PosPayment::class , 'register_id' , 'id' );
        }

        public function getExpectedFloatAttribute()
        {
            return $this->opening_float + $this->posPayments()->sum( 'amount' );
        }

        public function orders() : HasMany | Register
        {
            return $this->hasMany( Order::class,'register_id','id');
        }

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        protected function casts() : array
        {
            return [
                'closed_at' => 'datetime' ,
                'status'    => RegisterStatus::class
            ];
        }
    }

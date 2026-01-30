<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Address extends Model
    {
        use HasFactory;

        protected $table    = "addresses";
        protected $fillable = [ 'full_name' , 'country_id' , 'state_id' , 'city_id' , 'email' , 'country_code' , 'phone' , 'country' , 'address' , 'user_id' , 'state' ,
            'city' , 'zip_code' ,
            'latitude' , 'longitude', 'address_line', 'is_default', 'type'
        ];
        protected $casts    = [
            'id'           => 'integer' ,
            'full_name'    => 'string' ,
            'email'        => 'string' ,
            'country_code' => 'string' ,
            'phone'        => 'string' ,
            'country'      => 'string' ,
            'address'      => 'string' ,
            'user_id'      => 'integer' ,
            'state'        => 'string' ,
            'city'         => 'string' ,
            'zip_code'     => 'string' ,
            'country_id'   => 'integer' ,
            'state_id'     => 'integer' ,
            'city_id'      => 'integer' ,
            'latitude'     => 'string' ,
            'longitude'    => 'string' ,
            'address_line' => 'string',
            'is_default'   => 'integer',
            'type'         => 'string',
        ];

        public function user() : BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        public function country() : BelongsTo
        {
            return $this->belongsTo(Country::class);
        }
    }

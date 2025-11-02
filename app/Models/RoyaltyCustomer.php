<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class RoyaltyCustomer extends Model
    {
        use HasFactory;

        protected $guarded = [];
        protected $casts   = [
            'points' => 'integer' ,
        ];

        public function royaltyPackage()
        {
            return $this->hasOne(RoyaltyPackage::class , 'id' , 'package_id');
        }

        public function orders() : HasMany
        {
            return $this->hasMany(Order::class , 'user_id' , 'id')
                        ->where('user_type' , '=' , RoyaltyCustomer::class);
        }

        public function getImageAttribute() : string
        {
            return asset('images/default/profile.png');
        }

        public function referal()
        {
            return $this->hasOne(RoyaltyCustomer::class , 'customer_id' , 'referer');
        }

//        public function getQrCodeAttribute() : ?string
//        {
//            info($this->qr_code);
//            if ( ! empty($this->qr_code) ) {
//                return asset($this->qr_code);
//            }
//            return null;
//        }
    }

<?php

    namespace App\Models;

    use App\Enums\ServiceType;
    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class Service extends Model
    {
        protected $fillable = [
            'name' ,
            'service_category_id' ,
            'base_price' ,
            'duration' ,
            'description' , 'type' , 'service_type' ,
            'status'
        ];

        protected $casts = [ 'type' => ServiceType::class , 'status' => Status::class , 'base_price' => 'float' ];

        public function serviceCategory() : BelongsTo
        {
            return $this->belongsTo( ServiceCategory::class );
        }

        public function addOns() : HasMany
        {
            return $this->hasMany( ServiceAddOn::class );
        }

        public function items() : HasMany
        {
            return $this->hasMany( ServiceItem::class , 'service_id' , 'id' );
        }

        public function tiers() : HasMany
        {
            return $this->hasMany( ServiceTier::class , 'service_id' , 'id' );
        }

        public function orderAddOns() : HasMany
        {
            return $this->hasMany( OrderServiceAdon::class );
        }

        public function orderTier() : HasOne
        {
            return $this->hasOne( OrderServiceTier::class );
        }

        protected function stock() : Attribute
        {
            return Attribute::make(
                get: fn() => 0 ,
            );
        }


        protected function unit() : Attribute
        {
            return Attribute::make(
                get: fn() => NULL ,
            );
        }

    }

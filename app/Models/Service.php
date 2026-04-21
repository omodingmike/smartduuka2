<?php

    namespace App\Models;

    use App\Enums\ServiceType;
    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

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

        protected $casts = [ 'type' => ServiceType::class , 'status' => Status::class ];

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
    }

<?php

    namespace App\Models;

    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Unit extends Model
    {
        use HasFactory;

        protected $table    = "units";
        protected $fillable = [ 'name' , 'short_name' , 'status' , 'conversion_factor' , 'base_unit_id' ];
        protected $casts    = [
            'status'            => Status::class ,
            'conversion_factor' => 'integer' ,
        ];

        public function products() : HasMany
        {
            return $this->hasMany( Product::class )->where( [ 'status' => Status::ACTIVE ] );
        }

        public function getLabelAttribute()
        {
            return statusLabel( $this->status );
        }
    }

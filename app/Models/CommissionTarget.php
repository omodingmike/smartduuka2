<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Spatie\Permission\Models\Role;

    class CommissionTarget extends Model
    {
        protected $fillable = [
            'commission_id' ,
            'user_id' ,
            'role_id' ,
            'product_id' ,
            'product_variation_id' ,
        ];

        public function commission() : BelongsTo
        {
            return $this->belongsTo( Commission::class );
        }

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        public function role() : BelongsTo
        {
            return $this->belongsTo( Role::class );
        }

        public function product() : BelongsTo
        {
            return $this->belongsTo( Product::class );
        }

        public function productVariation() : BelongsTo
        {
            return $this->belongsTo( ProductVariation::class );
        }
    }

<?php

    namespace App\Models;

    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Model;

    class Commission extends Model
    {
        protected $fillable = [
            'name' ,
            'commission_type' ,
            'commission_value' ,
            'applies_to' ,
            'product_scope' ,
            'condition_json' ,
            'is_active' ,
        ];

        protected $casts = [
            'condition_json' => 'array' ,
            'is_active'      => Status::class ,
        ];

        public function targets()
        {
            return $this->hasMany( CommissionTarget::class );
        }

        public static function getApplicableCommission($user , $product , $variation = NULL)
        {
            return self::query()
                       ->join( 'commission_targets as targets' , 'targets.commission_id' , '=' , 'commissions.id' )
                       ->where( 'commissions.is_active' , Status::ACTIVE )
                       ->where( function ($q) use ($user , $product , $variation) {
                           $q->where( 'targets.user_id' , $user?->id )
                             ->orWhereIn( 'targets.role_id' , $user->roles()->pluck( 'id' )->toArray() )
                             ->orWhere( function ($inner) {
                                 $inner->whereNull( 'targets.user_id' )
                                       ->whereNull( 'targets.role_id' );
                             } );
                       } )
                       ->where( function ($q) use ($product , $variation) {
                           if ( $variation ) {
                               $q->where( 'targets.product_variation_id' , $variation->id );
                           }
                           $q->orWhere( 'targets.product_id' , $product->id )
                             ->orWhere( function ($inner) {
                                 $inner->whereNull( 'targets.product_id' )
                                       ->whereNull( 'targets.product_variation_id' );
                             } );
                       } )
                       ->orderByRaw( '
        CASE
            WHEN targets.user_id IS NOT NULL AND targets.product_variation_id IS NOT NULL THEN 1
            WHEN targets.user_id IS NOT NULL AND targets.product_id IS NOT NULL THEN 2
            WHEN targets.user_id IS NOT NULL THEN 3
            WHEN targets.role_id IS NOT NULL AND targets.product_variation_id IS NOT NULL THEN 4
            WHEN targets.role_id IS NOT NULL AND targets.product_id IS NOT NULL THEN 5
            WHEN targets.role_id IS NOT NULL THEN 6
            WHEN targets.user_id IS NULL AND targets.role_id IS NULL AND targets.product_variation_id IS NOT NULL THEN 7
            WHEN targets.user_id IS NULL AND targets.role_id IS NULL AND targets.product_id IS NOT NULL THEN 8
            ELSE 9
        END
    ' )
                       ->select( 'commissions.*' )
                       ->first();

        }
    }

<?php

    namespace App\Services;

    use App\Models\Commission;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\User;

    class CommissionService
    {
        /**
         * Return the most specific applicable commission for user + product + variation
         */
        public function getApplicableCommission(User $user , Product $product , ?ProductVariation $variation = NULL) : ?Commission
        {
            return Commission::query()
                             ->where( 'is_active' , TRUE )
                             ->where( function ($q) use ($user) {
                                 $q->where( 'applies_to' , 'all' )
                                   ->orWhereHas( 'targets' , function ($q2) use ($user) {
                                       $q2->where( 'user_id' , $user->id )
                                          ->orWhereIn( 'role_id' , $user->roles->pluck( 'id' ) );
                                   } );
                             } )
                             ->where( function ($q) use ($product , $variation) {
                                 $q->where( 'product_scope' , 'all' )
                                   ->orWhereHas( 'targets' , function ($q2) use ($product , $variation) {
                                       $q2->where( function ($inner) use ($variation) {
                                           if ( $variation ) {
                                               $inner->where( 'product_variation_id' , $variation->id );
                                           }
                                       } )
                                          ->orWhere( function ($inner) use ($product) {
                                              $inner->where( 'product_id' , $product->id )
                                                    ->whereNull( 'product_variation_id' );
                                          } )
                                          ->orWhere( function ($inner) {
                                              $inner->whereNull( 'product_id' )
                                                    ->whereNull( 'product_variation_id' );
                                          } );
                                   } );
                             } )
                             ->orderByRaw( '
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM commission_targets
                        WHERE commissions.id = commission_targets.commission_id
                        AND commission_targets.product_variation_id IS NOT NULL
                    ) THEN 1
                    WHEN EXISTS (
                        SELECT 1 FROM commission_targets
                        WHERE commissions.id = commission_targets.commission_id
                        AND commission_targets.product_id IS NOT NULL
                    ) THEN 2
                    ELSE 3
                END
            ' )->first();
        }

        /**
         * Calculate commission amount for a user + product + variation + sale amount.
         */
        public function calculateCommission(User $user , Product $product , float $saleAmount , ?ProductVariation $variation = NULL) : float
        {
            $commission = $this->getApplicableCommission( $user , $product , $variation );

            if ( ! $commission ) {
                return 0.0;
            }

            if ( $commission->commission_type === 'percentage' ) {
                return round( ( $saleAmount * ( $commission->commission_value / 100 ) ) , 2 );
            }

            return round( $commission->commission_value , 2 );
        }
    }

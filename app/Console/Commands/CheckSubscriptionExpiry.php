<?php

    namespace App\Console\Commands;

    use App\Enums\Status;
    use App\Enums\SubscriptionPaymentStatus;
    use App\Models\TenantSubscription;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Cache;
    use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

    class CheckSubscriptionExpiry extends Command
    {
        protected $signature = 'subscriptions:check-expiry';

        protected $description = 'Deactivate subscriptions that have passed their expiry date';

        /**
         * @throws TenantCouldNotBeIdentifiedById
         */
        public function handle() : void
        {

            try {
                $expiredTenantIds = TenantSubscription::query()
                                                      ->where( 'expires_at' , '<' , now() )
                                                      ->where( 'status' , Status::ACTIVE )
                                                      ->pluck( 'tenant_id' );
                if ( $expiredTenantIds->isEmpty() ) {
                    return;
                }
                $updated = TenantSubscription::query()
                                             ->whereIn( 'tenant_id' , $expiredTenantIds )
                                             ->where( 'payment_status' , SubscriptionPaymentStatus::Paid )
                                             ->where( 'status' , Status::ACTIVE )
                                             ->update( [ 'status' => Status::INACTIVE ] );
                if ( $updated )
                    foreach ( $expiredTenantIds as $expired_tenant_id ) {
                        tenancy()->initialize( $expired_tenant_id );
                        $cacheKey = "tenant_subscription_{$expired_tenant_id}";
                        Cache::forget( $cacheKey );
                        tenancy()->end();
                    }
            } catch ( TenantCouldNotBeIdentifiedById $e ) {
                info( $e->getMessage() );
            }
        }
    }

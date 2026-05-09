<?php

    namespace App\Http\Middleware;

    use App\Enums\Status;
    use App\Enums\SubscriptionPaymentStatus;
    use App\Models\TenantSubscription;
    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Symfony\Component\HttpFoundation\Response;

    class SubscribedMiddleware
    {
        public function handle(Request $request , Closure $next) : Response
        {
            $tenantId = tenant( 'id' );
            $cacheKey = "tenant_subscription_{$tenantId}";

            $subscription = Cache::remember( $cacheKey , now()->addMinutes( 10 ) , function () use ($tenantId) {
                $result = FALSE;

                tenancy()->central( function () use ($tenantId , &$result) {
                    $result = tenantSubscriptions( $tenantId )->exists();
                } );

                return $result;
            } );

//            if ( ! $subscription ) {
//                return response()->json( [
//                    'message' => 'Your subscription has expired. Please renew your subscription.'
//                ] , 203 );
//            }

            return $next( $request );
        }
    }

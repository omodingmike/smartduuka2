<?php

    namespace App\Http\Controllers;

    use App\Enums\Status;
    use App\Enums\SubscriptionPaymentStatus;
    use App\Http\Requests\SubscriptionPlanRequest;
    use App\Http\Resources\BillingCycleResource;
    use App\Http\Resources\SubscriptionPlanResource;
    use App\Models\BillingCycle;
    use App\Models\SubscriptionPlan;
    use App\Models\TenantSubscription;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;

    class SubscriptionPlanController extends Controller
    {
        public function index(Request $request)
        {
            $type = $request->integer( 'type' );
            return SubscriptionPlanResource::collection( SubscriptionPlan::where( 'type' , $type )->get() );
        }

        public function billingCycles()
        {
            return BillingCycleResource::collection( BillingCycle::all() );
        }

        public function store(SubscriptionPlanRequest $request)
        {
            return new SubscriptionPlanResource( SubscriptionPlan::create( $request->validated() ) );
        }

        public function show(SubscriptionPlan $subscriptionPlan)
        {
            return new SubscriptionPlanResource( $subscriptionPlan );
        }

        public function update(SubscriptionPlanRequest $request , SubscriptionPlan $subscriptionPlan)
        {
            $subscriptionPlan->update( $request->validated() );

            return new SubscriptionPlanResource( $subscriptionPlan );
        }

        public function destroy(SubscriptionPlan $subscriptionPlan)
        {
            $subscriptionPlan->delete();

            return response()->json();
        }

        public function subscribed()
        {
            $tenantId = tenant( 'id' );
            $cacheKey = "tenant_subscription_{$tenantId}";
            Cache::forget( $cacheKey );
            $subscribed = FALSE;

            tenancy()->central( function () use ($tenantId , &$subscribed) {
                $subscribed = TenantSubscription::where( 'expires_at' , '>=' , now() )
                                                ->where( 'payment_status' , '=' , SubscriptionPaymentStatus::Paid )
                                                ->where( 'status' , '=' , Status::ACTIVE )
                                                ->where( 'tenant_id' , $tenantId )
                                                ->exists();
            } );

            if ( ! $subscribed ) {
                return response()->json( [
                    'data' => [ 'subscribed' => FALSE ]
                ] );
            }
            return response()->json( [
                'data' => [ 'subscribed' => TRUE ]
            ] );
        }
    }

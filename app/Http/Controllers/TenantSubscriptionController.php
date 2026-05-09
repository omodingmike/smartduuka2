<?php

    namespace App\Http\Controllers;

    use App\Enums\Status;
    use App\Http\Requests\TenantSubscriptionRequest;
    use App\Http\Resources\TenantSubscriptionResource;
    use App\Jobs\InitiatePaymentJob;
    use App\Models\BillingCycle;
    use App\Models\TenantSubscription;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class TenantSubscriptionController extends Controller
    {
        public function index(Request $request)
        {
            $page          = $request->integer( 'page' );
            $per_page      = $request->integer( 'per_page' );
            $tenant        = $request->string( 'tenant' );
            $subscriptions = TenantSubscription::with( [ 'billingCycle' , 'subscriptionPlan' ] )
                                               ->where( 'tenant_id' , $tenant )
                                               ->latest()
                                               ->paginate( $per_page , [ '*' ] , 'page' , $page );
            return TenantSubscriptionResource::collection( $subscriptions );
        }

        public function store(TenantSubscriptionRequest $request)
        {
            try {
                return DB::transaction( function () use ($request) {
                    $data         = $request->validated();
                    $subscription = TenantSubscription::create( [
                        'phone'                => $data[ 'phone' ] ,
                        'amount'               => $data[ 'amount' ] ,
                        'billing_cycle_id'     => $data[ 'billingCycle' ] ,
                        'tenant_id'            => $data[ 'tenant' ] ,
                        'subscription_plan_id' => $data[ 'subscriptionPlan' ] ,
                        'status'               => Status::INACTIVE ,
                    ] );

                    $cycle = BillingCycle::find( $data[ 'billingCycle' ] );

                    $activeSubscription = tenantSubscriptions( $data[ 'tenant' ] )->first();
                    $expiryBase         = $activeSubscription ? $activeSubscription->expires_at : now();

                    $subscription->update( [
                        'invoice_no' => recordId( 'INV' , $subscription ) ,
                        'expires_at' => $expiryBase->addMonths( $cycle->multiplier ) ,
                    ] );

                    InitiatePaymentJob::dispatch( $subscription );

                    return response()->json();
                } );
            } catch ( \Throwable $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }
        }

        public function destroy(TenantSubscription $tenantSubscription)
        {
            $tenantSubscription->delete();

            return response()->json();
        }
    }
